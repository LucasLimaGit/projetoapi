<?php 
namespace Models;

use \Core\Model;
use \Models\Jwt;
use \Models\Drinks;

class Users extends Model {
    
    private $id_user;
    
    public function create($name, $email, $pass) {
        
        if(!$this->emailExists($email)) {            
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :pass)";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(':name', $name);
            $sql->bindValue(':email', $email);
            $sql->bindValue(':pass', $hash);
            $sql->execute();
            
            $this->id_user = $this->db->lastInsertId();
            
            return true;
        } else {
            return false;
        }
    }
    
    public function checkCredentials($email, $pass) {
        
        $sql = "SELECT id, password FROM users WHERE email = :email";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':email', $email);
        $sql->execute();
        
        if($sql->rowCount() > 0) {
            $info = $sql->fetch();
            
            if(password_verify($pass, $info['password'])) {
                $this->id_user = $info['id'];
                
                return true;                
            } else {
                return false;
            }            
        } else {
            return false;
        }
        
    }
    
    public function getId() {
        return $this->id_user;
    }
    
    public function getInfo($id) {        
        $array = array();
        
        $sql = "SELECT id, name, email FROM users WHERE id = :id";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':id', $id);
        $sql->execute();
        
        if($sql->rowCount() > 0) {
            $array = $sql->fetch(\PDO::FETCH_ASSOC);
            
            $drinks = new Drinks();
            $array['drink_counter'] = $drinks->getDrinkCounter($id);
        }      
        
        return $array;
    }
    
    public function getInfoAll($offset, $limit) {
        $array = array();
        
        $sql = "SELECT id, name, email FROM users LIMIT :offset, :limit";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $sql->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $sql->execute();
        
        if($sql->rowCount() > 0) {
            $array = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        return $array;
    }   
    
    public function getTotal() {
        
        $sql = "SELECT COUNT(*) AS c FROM users";
        $sql = $this->db->prepare($sql);
        $sql->execute();
        $info = $sql->fetch();
        
        return $info['c'];
    }
    
    public function createJwt($login = false) {
        $jwt = new Jwt();
        if(!$login) {
            return $jwt->create(array('id_user'=>$this->id_user));            
        } else {
            $array = array();
            $array['jwt'] = $jwt->create(array('id_user'=>$this->id_user));
            $array['data'] = $this->getInfo($this->id_user);
            return $array;
        }
    }
    
    public function validateJwt($token) {
        $jwt = new Jwt();
        $info = $jwt->validate($token);
                
        if(isset($info->id_user)) {
            $this->id_user = $info->id_user;
            return true;
        } else {
            return false;
        }
    }
    
    private function emailExists($email) {
        
        $sql = "SELECT id FROM users WHERE email = :email";
        $sql = $this->db->prepare($sql);
        $sql->bindValue(':email', $email);
        $sql->execute();
        
        if($sql->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
        
    }
    
    public function editInfo($id, $data) {
        
        if($id === $this->getId()) {
            
            $toChange = array();
            
            if(!empty($data['name'])) {
                $toChange['name'] = $data['name'];
            }
            if(!empty($data['email'])) {
                if(filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    if(!$this->emailExists($data['email'])) {
                        $toChange['email'] = $data['email'];                        
                    } else {
                        return 'E-mail já existente.';
                    }
               } else {
                   return 'E-mail inválido.';
               }
            }
            if(!empty($data['password'])) {                
                $toChange['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            if(count($toChange) > 0) {
                $fields = array();
                foreach($toChange as $k => $v) {
                    $fields[] = $k.' = :'.$k;
                }
                
                $sql = "UPDATE users SET ".implode(',', $fields)." WHERE id = :id";
                $sql = $this->db->prepare($sql);
                $sql->bindValue(':id', $id);
                
                foreach($toChange as $k => $v) {
                    $sql->bindValue(':'.$k, $v);
                }
                
                $sql->execute();                
                return '';
            } else {
                return 'Dados não preenchidos.';
            }
            
        } else {
            return 'Não é permitido editar outro usuário.';
        }
    }
    
    /*
     * O ideal seria ter um campo status na tabela users, onde este definiria em qual 
     * situação o usuário se encontra, pois dessa maneira não iria ter perdas de dados 
     * e seria possível controlar o acesso de acordo com o tipo de status.
     */
    public function delete($id) {

        if($id === $this->getId()) {
            
            $drinks = new Drinks();
            $drinks->deleteAll($id);
            
            $sql = "DELETE FROM users WHERE id = :id";
            $sql = $this->db->prepare($sql);
            $sql->bindValue(':id', $id);
            $sql->execute();   
            
            return '';
        } else {
            return 'Não é permitido excluir outro usuário.';
        }
        
    }
    
}