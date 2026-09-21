<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
class Cms {
 private PDO $db;
 public function __construct(){ $this->db=Database::getConnection(); }
 public function content(): array {$s=$this->db->query('SELECT * FROM website_content ORDER BY section_name,content_key');$out=[];foreach($s->fetchAll() as $r)$out[$r['content_key']]=$r;return $out;}
 public function saveContent(string $key,string $value,string $section='General'): void {$s=$this->db->prepare('INSERT INTO website_content (section_name,content_key,content_value) VALUES (?,?,?) ON DUPLICATE KEY UPDATE section_name=VALUES(section_name),content_value=VALUES(content_value)');$s->execute([$section,$key,$value]);}
 public function settings(): array {$s=$this->db->query('SELECT * FROM website_settings ORDER BY setting_key');$out=[];foreach($s->fetchAll() as $r)$out[$r['setting_key']]=$r;return $out;}
 public function saveSetting(string $key,string $value): void {$s=$this->db->prepare('INSERT INTO website_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$s->execute([$key,$value]);}
}
