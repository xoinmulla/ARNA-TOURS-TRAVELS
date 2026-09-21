<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
class Driver {
 private PDO $db;
 public function __construct(){ $this->db=Database::getConnection(); }
 public function all(?string $search=null, ?string $status=null): array {$sql='SELECT d.*,v.vehicle_name,v.registration_number FROM drivers d LEFT JOIN vehicles v ON v.id=d.vehicle_id WHERE 1=1';$p=[];if($search){$sql.=' AND (d.full_name LIKE :q OR d.mobile_number LIKE :q OR d.license_number LIKE :q)';$p[':q']='%'.$search.'%';}if($status&&in_array($status,['AVAILABLE','ASSIGNED','OFF_DUTY','INACTIVE'],true)){$sql.=' AND d.status=:status';$p[':status']=$status;}$sql.=' ORDER BY d.full_name';$s=$this->db->prepare($sql);$s->execute($p);return $s->fetchAll();}
 public function find(int $id): ?array {$s=$this->db->prepare('SELECT * FROM drivers WHERE id=?');$s->execute([$id]);$r=$s->fetch();return $r?:null;}
 public function create(array $d): int {$s=$this->db->prepare('INSERT INTO drivers (full_name,mobile_number,email,license_number,vehicle_id,status,notes) VALUES (?,?,?,?,?,?,?)');$s->execute([$d['full_name'],$d['mobile_number'],$d['email']?:null,$d['license_number']?:null,$d['vehicle_id']?:null,$d['status'],$d['notes']?:null]);return (int)$this->db->lastInsertId();}
 public function update(int $id,array $d): bool {$s=$this->db->prepare('UPDATE drivers SET full_name=?,mobile_number=?,email=?,license_number=?,vehicle_id=?,status=?,notes=? WHERE id=?');return $s->execute([$d['full_name'],$d['mobile_number'],$d['email']?:null,$d['license_number']?:null,$d['vehicle_id']?:null,$d['status'],$d['notes']?:null,$id]);}
 public function delete(int $id): bool {$s=$this->db->prepare('DELETE FROM drivers WHERE id=?');return $s->execute([$id]);}
}
