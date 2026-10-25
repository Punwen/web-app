<?php
declare(strict_types=1);
require_once __DIR__.'/domain.php';
function connect_database(array $config): PDO {
    $dsn=sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $config['host'],$config['port'],$config['database']);
    $db=new PDO($dsn,$config['username'],$config['password'],[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false,
        PDO::ATTR_TIMEOUT=>3,
    ]);
    $db->exec("SET time_zone = '+00:00'");
    return $db;
}
final class SaveRepository {
    public function __construct(private PDO $db) {}
    public function create(array $values): int {
        $query=$this->db->prepare('INSERT INTO saves (title,category,status,progress,next_step,memo,created_at,updated_at) VALUES (:title,:category,:status,:progress,:next_step,:memo,UTC_TIMESTAMP(6),UTC_TIMESTAMP(6))');
        $query->execute($this->fields($values));
        return (int)$this->db->lastInsertId();
    }
    public function find(int $id): ?array {
        $query=$this->db->prepare('SELECT * FROM saves WHERE id=?'); $query->execute([$id]);
        return $query->fetch()?:null;
    }
    public function update(int $id,int $version,array $values): bool {
        $query=$this->db->prepare('UPDATE saves SET title=:title, category=:category, status=:status, progress=:progress, next_step=:next_step, memo=:memo, updated_at=UTC_TIMESTAMP(6), version=version+1 WHERE id=:id AND version=:version');
        $query->execute($this->fields($values)+['id'=>$id,'version'=>$version]);
        return $query->rowCount()===1;
    }
    public function delete(int $id,int $version): bool {
        $query=$this->db->prepare('DELETE FROM saves WHERE id=? AND version=?'); $query->execute([$id,$version]);
        return $query->rowCount()===1;
    }
    public function listing(string $status='all',string $category='all',int $page=1): array {
        $where=[]; $args=[];
        if ($status!=='all') { if(!in_array($status,SAVE_STATUSES,true)) throw new InvalidArgumentException('status'); $where[]='status=?'; $args[]=$status; }
        if ($category!=='all') { if(!in_array($category,SAVE_CATEGORIES,true)) throw new InvalidArgumentException('category'); $where[]='category=?'; $args[]=$category; }
        $sql=$where?' WHERE '.implode(' AND ',$where):'';
        $query=$this->db->prepare('SELECT COUNT(*) FROM saves'.$sql); $query->execute($args); $total=(int)$query->fetchColumn();
        $pages=max(1,(int)ceil($total/12)); $page=max(1,min($page,$pages));
        $query=$this->db->prepare('SELECT * FROM saves'.$sql.' ORDER BY updated_at DESC,id DESC LIMIT 12 OFFSET '.(($page-1)*12));
        $query->execute($args);
        return ['items'=>$query->fetchAll(),'total'=>$total,'page'=>$page,'pages'=>$pages];
    }
    public function counts(): array {
        $counts=['all'=>0,'active'=>0,'paused'=>0,'completed'=>0];
        foreach($this->db->query('SELECT status,COUNT(*) AS total FROM saves GROUP BY status') as $row) {
            $counts[$row['status']]=(int)$row['total']; $counts['all']+=(int)$row['total'];
        }
        return $counts;
    }
    public function latestActive(): ?array {
        return $this->db->query("SELECT * FROM saves WHERE status='active' ORDER BY updated_at DESC,id DESC LIMIT 1")->fetch()?:null;
    }
    private function fields(array $values): array {
        return array_intersect_key($values,array_flip(['title','category','status','progress','next_step','memo']));
    }
}
