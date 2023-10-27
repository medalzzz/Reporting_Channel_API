<?php 
namespace app\resources;

use \app\database\Connection;
use PDO;
use \Flight;

class Resource_Model{
   private PDO $con;
   
   public function __construct(){
      $con = new Connection(); 
      $this->con = $con->getConnection();
   }

   /**
   * Returns multiple resources from database.
   * 
   * @return string
   */
   public function getResources() : string{
      $bindings = [];
      $filters = "";
      $order = "";
      $limit = "";

      //FILTERS
      if(isset($_GET["deleted"]) && $_GET["deleted"] !== null && $_GET["deleted"] !== ""){ 
         $filters .= " AND deleted = :deleted"; 
         $bindings[':deleted'] = $_GET['deleted'];
      }

      if(isset($_GET["type"]) && $_GET["type"] !== null && $_GET["type"] !== ""){ 
         $filters .= " AND type LIKE :type"; 
         $bindings[':type'] = "%".$_GET['type']."%"; 
      }

      //ORDER BY
      if(isset($_GET["order"]) && $_GET["order"] !== null && $_GET["order"] !== ""){
         $allowed_columns = ["id", "type", "message", "is_identified", "whistleblower_name", "whistleblower_birth", "deleted", "created_at"];
         $allowed_dir = ["ASC", "DESC"];

         $order_by = in_array($_REQUEST["order"], $allowed_columns) ? $_REQUEST["order"] : 'id';
         $direction = (isset($_REQUEST["order_dir"]) && in_array(strtoupper($_REQUEST["order_dir"]), $allowed_dir)) ? $_REQUEST["order_dir"] : 'ASC';
         
         $order = "ORDER BY $order_by $direction";
      }

      //LIMIT && OFFSET
      if((isset($_GET["limit"]) && $_GET["limit"] !== null && $_GET["limit"] !== "")){
         $limit = "LIMIT {$_REQUEST["limit"]}";
         (isset($_GET["offset"]) && $_GET["offset"] !== null && $_GET["offset"] !== "") ? $limit .= ", {$_REQUEST["offset"]}" : "";
      }

      // default query
      $sql = "SELECT * FROM registros WHERE 1 $filters $order $limit";
      $stmt = $this->con->prepare($sql);
      $stmt->execute($bindings);
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      //total entries with limit to show per page
      $filtered_resources = count($data);

      //total entries without limit for pagination
      $sql_no_limit = "SELECT COUNT(*) FROM registros WHERE 1 $filters";
      $stmt = $this->con->prepare($sql_no_limit);
      $stmt->execute($bindings);
      $total = (int) $stmt->fetchColumn();

      $num_pages = ceil($total / $filtered_resources);
      
      $response = [
         'total' => $total, // total number of records without any filters
         'filtered' => $filtered_resources, // total number of records after applying filters
         'pages' => $num_pages, //number of pages needed to display all entries
         'data' => $data // the array of records fetched
      ];

      return json_encode($response);
   }

   /**
   * Returns a resource from database.
   * 
   * @return string
   */
   public function getResource(string $id) : string{
      $bindings = [];

      $sql = "SELECT * FROM registros WHERE id = :id";
      $bindings[':id'] = $id;

      $stmt = $this->con->prepare($sql);
      $stmt->execute($bindings);
      $data = $stmt->fetch(PDO::FETCH_ASSOC);

      if($data) return json_encode($data); //if resource is found, returns it
      
      //returns error msg if resource was not found
      Flight::response()->status(404);
      return json_encode(["message" => "Resource not found"]);
   }

   /**
   * Inserts a new resource in the database.
   * 
   * @return string
   */
   public function insertRegistro() : string{
      $created_at = date("Y-m-d H:i:s");
      $whistleblower_name = $_POST["whistleblower_name"] ?? null;
      $whistleblower_birth = $_POST["whistleblower_birth"] ?? null;
      
      $sql = "INSERT INTO registros
               (type, 
               message, 
               is_identified,
               whistleblower_name, 
               whistleblower_birth, 
               created_at,
               deleted) 
               VALUES (?, ?, ?, ?, ?, ?, ?)";
      $stmt = $this->con->prepare($sql);
      $stmt->execute([$_POST["type"], $_POST["message"], $_POST["is_identified"], $whistleblower_name, $whistleblower_birth, $created_at, $_POST["deleted"]]);
      $id = $this->con->lastInsertId();

      Flight::response()->status(201); //created

      $sql = "SELECT * FROM registros WHERE id = ?";
      $stmt = $this->con->prepare($sql);
      $stmt->execute([$id]);
      $data = $stmt->fetch(PDO::FETCH_ASSOC);

      return json_encode($data);
   }

   /**
   * Updates a resource in the database then returns updated resource|resource not found.
   * 
   * @return string
   */
   public function updateRegistro(string $id) : string{
      $bindings = [];
      $update_clauses = [];

      $keys = ["type", "message", "is_identified", "whistleblower_name", "deleted"];

      foreach ($keys as $key) {
         if (isset($_POST[$key]) && $_POST[$key] !== null && $_POST[$key] !== ""){
            $update_clauses[] = "$key = :$key";
            $bindings[":$key"] = $_POST[$key];
         }
      }

      $update_clauses = implode(", ", $update_clauses);
      $sql = "UPDATE registros SET $update_clauses WHERE id = :id";
      $bindings[":id"] = $id;

      $stmt = $this->con->prepare($sql);
      $stmt->execute($bindings);

      $sql = "SELECT * FROM registros WHERE id = ?";
      $stmt = $this->con->prepare($sql);
      $stmt->execute([$id]);
      $data = $stmt->fetch(PDO::FETCH_ASSOC);
      
      if($data) return json_encode($data); //if resource is found, returns it
      
      //returns error msg if resource was not found
      Flight::response()->status(404);
      return json_encode(["message" => "Resource not found"]);
   }

   /**
   * Deletes a resource in the database then returns success|not found message.
   * 
   * @return string
   */
   public function deleteRegistro(string $id) : string{
      $sql = "DELETE FROM registros WHERE id = ?";
      $stmt = $this->con->prepare($sql);
      $stmt->execute([$id]);

      //returns the id of the deleted row if if exists
      if($stmt->rowCount() > 0) return json_encode(["message" => "Resource $id deleted"]);

      //returns an error message if resource with this id was not found
      Flight::response()->status(404);
      return json_encode(["message" => "Resource not found"]);
   }
}