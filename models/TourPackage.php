<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

class TourPackage {
 private PDO $db;
 public function __construct(){ $this->db=Database::getConnection(); }
 public function all(?string $search=null, ?string $status=null, ?int $categoryId=null): array {
  $sql='SELECT tp.*, tpc.title AS category_title, tpc.slug AS category_slug FROM tour_packages tp LEFT JOIN tour_package_categories tpc ON tpc.id=tp.category_id WHERE 1=1'; $p=[];
  if($search){$sql.=' AND (tp.package_name LIKE :q OR tp.destination LIKE :q)';$p[':q']='%'.$search.'%';}
  if($status && in_array($status,['ACTIVE','INACTIVE'],true)){$sql.=' AND tp.status=:status';$p[':status']=$status;}
  if($categoryId){$sql.=' AND tp.category_id=:category_id';$p[':category_id']=$categoryId;}
  $sql.=' ORDER BY tp.status="INACTIVE", tp.package_name ASC'; $s=$this->db->prepare($sql);$s->execute($p);return $s->fetchAll();
 }
 public function activeByCategorySlug(string $slug): array {
  $s=$this->db->prepare("SELECT tp.*, tpc.title AS category_title, tpc.slug AS category_slug FROM tour_packages tp INNER JOIN tour_package_categories tpc ON tpc.id=tp.category_id WHERE tp.status='ACTIVE' AND tpc.status='ACTIVE' AND tpc.slug=? ORDER BY tp.package_name ASC");
  $s->execute([$slug]); return $s->fetchAll();
 }
 public function categories(bool $activeOnly=false): array {
  $sql='SELECT * FROM tour_package_categories'; if($activeOnly)$sql.=" WHERE status='ACTIVE'"; $sql.=' ORDER BY sort_order ASC,id ASC'; return $this->db->query($sql)->fetchAll();
 }
 public function featuredHome(int $limit=6): array {
  $limit=max(1,min(12,$limit));
  $s=$this->db->query("SELECT tp.*, tpc.title AS category_title, tpc.slug AS category_slug FROM tour_packages tp LEFT JOIN tour_package_categories tpc ON tpc.id=tp.category_id WHERE tp.status='ACTIVE' AND tp.featured_home=1 ORDER BY tp.home_sort_order ASC, tp.package_name ASC LIMIT ".$limit);
  return $s->fetchAll();
 }
 public function updateHomeSettings(int $id,bool $featured,int $sortOrder): bool {
  $s=$this->db->prepare('UPDATE tour_packages SET featured_home=?, home_sort_order=? WHERE id=?');
  return $s->execute([$featured?1:0,$sortOrder,$id]);
 }
 public function category(int $id): ?array {$s=$this->db->prepare('SELECT * FROM tour_package_categories WHERE id=? LIMIT 1');$s->execute([$id]);$r=$s->fetch();return $r?:null;}
 public function createCategory(array $d): int {$s=$this->db->prepare('INSERT INTO tour_package_categories(title,slug,sort_order,status) VALUES(?,?,?,?)');$s->execute([$d['title'],$d['slug'],$d['sort_order'],$d['status']]);return (int)$this->db->lastInsertId();}
 public function updateCategory(int $id,array $d): bool {$s=$this->db->prepare('UPDATE tour_package_categories SET title=?,slug=?,sort_order=?,status=? WHERE id=?');return $s->execute([$d['title'],$d['slug'],$d['sort_order'],$d['status'],$id]);}
 public function deleteCategory(int $id): bool {$s=$this->db->prepare('DELETE FROM tour_package_categories WHERE id=?');return $s->execute([$id]);}
 public function find(int $id): ?array {$s=$this->db->prepare('SELECT * FROM tour_packages WHERE id=? LIMIT 1');$s->execute([$id]);$r=$s->fetch();return $r?:null;}
 public function create(array $d): int {$s=$this->db->prepare('INSERT INTO tour_packages (package_name,destination,category_id,duration,price,image,description,status) VALUES (?,?,?,?,?,?,?,?)');$s->execute([$d['package_name'],$d['destination'],$d['category_id']?:null,$d['duration']?:null,$d['price']!==''?$d['price']:null,$d['image']?:null,$d['description']?:null,$d['status']]);return (int)$this->db->lastInsertId();}
 public function update(int $id,array $d): bool {$s=$this->db->prepare('UPDATE tour_packages SET package_name=?,destination=?,category_id=?,duration=?,price=?,image=?,description=?,status=? WHERE id=?');return $s->execute([$d['package_name'],$d['destination'],$d['category_id']?:null,$d['duration']?:null,$d['price']!==''?$d['price']:null,$d['image']?:null,$d['description']?:null,$d['status'],$id]);}
 public function delete(int $id): bool {$s=$this->db->prepare('DELETE FROM tour_packages WHERE id=?');return $s->execute([$id]);}
}
