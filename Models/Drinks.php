<?php 
namespace Models;

use \Core\Model;

class Drinks extends Model {    
    
    public function create($id_user, $drink_ml) {
        
        $sql = "INSERT INTO drinks (id_user, drink_ml, date) VALUES (:id_user, :drink_ml, :date)";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id_user', $id_user);
        $sql->bindValue(':drink_ml', $drink_ml);
        $sql->bindValue(':date', date("Y-m-d H:i:s"));
        $sql->execute();
        
    }
    
    public function getDrinkCounter($id_user) {
        
        $sql = "SELECT COUNT(*) AS c FROM drinks WHERE id_user = :id_user";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();
        $info = $sql->fetch();
        
        return $info['c'];
        
    }
    
    public function getInfoAll($id_user) {
        $array = array();
        
        $sql = "SELECT SUM(drink_ml) AS mls, DATE_FORMAT(date, '%Y-%m-%d') AS date, COUNT(id) AS c 
                FROM drinks WHERE id_user = :id_user 
                GROUP BY DATE_FORMAT(date, '%Y-%m-%d')";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();
        
        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        return $array;
    }
    
    public function getRanking() {
        $array = array();
        
        $sql = "SELECT u.name, SUM(d.drink_ml) AS mls
                FROM users AS u INNER JOIN drinks AS d ON(u.id = d.id_user)
                WHERE DATE_FORMAT(d.date, '%Y-%m-%d') = :date 
                GROUP BY u.id ORDER BY mls DESC LIMIT 3";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':date', date("Y-m-d"));
        $sql->execute();
        
        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        return $array;
    }
    
    public function deleteAll($id_user) {
        
        $sql = "DELETE FROM drinks WHERE id_user = :id_user";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id_user', $id_user);
        $sql->execute();
        
    }
    
}