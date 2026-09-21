<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
class Destination {
 private PDO $db;
 public function __construct(){ $this->db=Database::getConnection(); }
 public function all(?string $search=null, ?string $status=null): array {
  $sql='SELECT * FROM destinations WHERE 1=1';$p=[];
  if($search){$sql.=' AND (name LIKE :q OR state LIKE :q OR country LIKE :q)';$p[':q']='%'.$search.'%';}
  if($status && in_array($status,['ACTIVE','INACTIVE'],true)){$sql.=' AND status=:status';$p[':status']=$status;}
  $sql.=' ORDER BY status="INACTIVE", name ASC';$s=$this->db->prepare($sql);$s->execute($p);return $s->fetchAll();
 }
 public function find(int $id): ?array {$s=$this->db->prepare('SELECT * FROM destinations WHERE id=? LIMIT 1');$s->execute([$id]);$r=$s->fetch();return $r?:null;}
 public function create(array $d): int {$s=$this->db->prepare('INSERT INTO destinations (name,state,country,latitude,longitude,description,image,status) VALUES (?,?,?,?,?,?,?,?)');$s->execute([$d['name'],$d['state']?:null,$d['country']?:'India',$d['latitude']!==''?$d['latitude']:null,$d['longitude']!==''?$d['longitude']:null,$d['description']?:null,$d['image']?:null,$d['status']]);return (int)$this->db->lastInsertId();}
 public function update(int $id,array $d): bool {$s=$this->db->prepare('UPDATE destinations SET name=?,state=?,country=?,latitude=?,longitude=?,description=?,image=?,status=? WHERE id=?');return $s->execute([$d['name'],$d['state']?:null,$d['country']?:'India',$d['latitude']!==''?$d['latitude']:null,$d['longitude']!==''?$d['longitude']:null,$d['description']?:null,$d['image']?:null,$d['status'],$id]);}
 public function delete(int $id): bool {$s=$this->db->prepare('DELETE FROM destinations WHERE id=?');return $s->execute([$id]);}
}
