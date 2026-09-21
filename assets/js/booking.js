document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('bookingForm');
  const alertBox = document.getElementById('bookingAlert');
  const submitButton = document.getElementById('bookingSubmit');
  const submitText = document.querySelector('.booking-submit-text');
  const submitLoading = document.querySelector('.booking-submit-loading');
  const successModal = document.getElementById('bookingSuccessModal');
  const bookingNumber = document.getElementById('bookingNumber');
  const dateInput = document.getElementById('bookingDate');
  const mobileInput = document.getElementById('bookingMobile');
  const participantsInput = document.getElementById('bookingParticipants');

  if (!form) return;

  // Prevent selecting a date in the past.
  if (dateInput) {
    const siteToday = form.dataset.siteToday;
    if (siteToday) dateInput.min = siteToday;
  }

  // Keep mobile input numeric.
  mobileInput?.addEventListener('input', () => {
    mobileInput.value = mobileInput.value.replace(/\D/g, '').slice(0, 10);
  });

  participantsInput?.addEventListener('input', () => {
    const value = parseInt(participantsInput.value || '1', 10);
    if (value > 100) participantsInput.value = '100';
    if (value < 1 && participantsInput.value !== '') participantsInput.value = '1';
  });

  function clearErrors() {
    form.querySelectorAll('.booking-field').forEach(field => field.classList.remove('has-error'));
    form.querySelectorAll('.booking-error').forEach(error => { error.textContent = ''; });
  }

  function setFieldError(name, message) {
    const error = form.querySelector(`[data-error-for="${name}"]`);
    const field = error?.closest('.booking-field');
    if (field) field.classList.add('has-error');
    if (error) error.textContent = message;
  }

  function showAlert(type, message) {
    alertBox.className = `booking-alert ${type}`;
    alertBox.textContent = message;
    alertBox.classList.remove('hidden');
  }

  function hideAlert() {
    alertBox.className = 'booking-alert hidden';
    alertBox.textContent = '';
  }

  function validateForm() {
    clearErrors();
    hideAlert();

    const data = new FormData(form);
    const fullName = String(data.get('full_name') || '').trim();
    const mobile = String(data.get('mobile_number') || '').trim();
    const source = String(data.get('source_location') || '').trim();
    const destination = String(data.get('destination_location') || '').trim();
    const date = String(data.get('preferred_date') || '').trim();
    const participants = Number(data.get('participants'));
    let valid = true;

    if (fullName.length < 2) {
      setFieldError('full_name', 'Please enter your full name.');
      valid = false;
    }

    if (!/^[6-9]\d{9}$/.test(mobile)) {
      setFieldError('mobile_number', 'Enter a valid 10-digit mobile number.');
      valid = false;
    }

    if (source.length < 2) {
      setFieldError('source_location', 'Please enter your pickup location.');
      valid = false;
    }

    if (destination.length < 2) {
      setFieldError('destination_location', 'Please enter your destination.');
      valid = false;
    }

    if (source && destination && source.toLowerCase() === destination.toLowerCase()) {
      setFieldError('destination_location', 'Pickup and destination should be different.');
      valid = false;
    }

    if (!date) {
      setFieldError('preferred_date', 'Please select your travel date.');
      valid = false;
    } else {
      const selected = new Date(`${date}T00:00:00`);
      const siteToday = form.dataset.siteToday || new Date().toISOString().slice(0, 10);
      const today = new Date(`${siteToday}T00:00:00`);
      if (Number.isNaN(selected.getTime()) || selected < today) {
        setFieldError('preferred_date', 'Travel date cannot be in the past.');
        valid = false;
      }
    }

    if (!Number.isInteger(participants) || participants < 1 || participants > 100) {
      setFieldError('participants', 'Enter a number between 1 and 100.');
      valid = false;
    }

    return valid;
  }

  function setLoading(loading) {
    submitButton.disabled = loading;
    submitText?.classList.toggle('hidden', loading);
    submitLoading?.classList.toggle('hidden', !loading);
  }

  function openSuccessModal(number) {
    bookingNumber.textContent = number || 'Booking received';
    successModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }

  function closeSuccessModal() {
    successModal.classList.add('hidden');
    document.body.style.overflow = '';
  }

  successModal?.querySelectorAll('[data-booking-close]').forEach(button => {
    button.addEventListener('click', closeSuccessModal);
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && successModal && !successModal.classList.contains('hidden')) {
      closeSuccessModal();
    }
  });

  form.addEventListener('submit', async event => {
    event.preventDefault();

    if (!validateForm()) {
      showAlert('error', 'Please correct the highlighted fields and try again.');
      form.querySelector('.has-error input')?.focus();
      return;
    }

    setLoading(true);

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 15000);

    try {
      const response = await fetch('api/booking/create.php', {
        method: 'POST',
        headers: {
          'Accept': 'application/json'
        },
        body: new FormData(form),
        signal: controller.signal
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        showAlert('error', result.message || 'Unable to submit your booking right now.');
        return;
      }

      form.reset();
      clearErrors();
      hideAlert();
      if (dateInput) {
        const today = new Date();
        dateInput.min = form.dataset.siteToday || new Date().toISOString().slice(0, 10);
      }
      if (participantsInput) participantsInput.value = '1';
      const defaultTrip = form.querySelector('input[name="trip_type"][value="ROUND_TRIP"]');
      if (defaultTrip) defaultTrip.checked = true;

      openSuccessModal(result.data?.booking_number);
    } catch (error) {
      console.error('Booking submission error:', error);
      showAlert('error', error?.name === 'AbortError' ? 'The booking service took too long to respond. Please try again.' : 'Unable to connect to the booking service. Please try again or call Arna directly.');
    } finally {
      clearTimeout(timeoutId);
      setLoading(false);
    }
  });
});
