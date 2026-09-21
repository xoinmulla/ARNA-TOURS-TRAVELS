<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

class Testimonial {
    private PDO $db;
    public function __construct(){ $this->db=Database::getConnection(); }
    public function all(bool $activeOnly=false): array {
        $sql='SELECT * FROM testimonials';
        if($activeOnly) $sql.=" WHERE status='ACTIVE'";
        $sql.=' ORDER BY created_at DESC, id DESC';
        return $this->db->query($sql)->fetchAll();
    }
    public function find(int $id): ?array {
        $s=$this->db->prepare('SELECT * FROM testimonials WHERE id=? LIMIT 1'); $s->execute([$id]); $r=$s->fetch(); return $r?:null;
    }
    public function create(array $d): int {
        $s=$this->db->prepare('INSERT INTO testimonials (customer_name,message,rating,status) VALUES (?,?,?,?)');
        $s->execute([$d['customer_name'],$d['message'],$d['rating'],$d['status']]); return (int)$this->db->lastInsertId();
    }
    public function update(int $id,array $d): bool {
        $s=$this->db->prepare('UPDATE testimonials SET customer_name=?,message=?,rating=?,status=? WHERE id=?');
        return $s->execute([$d['customer_name'],$d['message'],$d['rating'],$d['status'],$id]);
    }
    public function delete(int $id): bool { $s=$this->db->prepare('DELETE FROM testimonials WHERE id=?'); return $s->execute([$id]); }
}
