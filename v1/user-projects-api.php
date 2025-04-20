<?php
ini_set("display_errors", 1);

require '../vendor/autoload.php';

use \Firebase\JWT\JWT;

use \Firebase\JWT\Key;

//including headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// including files
include_once("../config/database.php");
include_once("../classes/Users.php");

//objects
$db = new Database();

$connection = $db->connect();

$user_obj = new Users($connection);

if ($_SERVER['REQUEST_METHOD'] === "GET") {

  $headers = getallheaders();

  if (isset($headers['Authorization'])) {

    

    try {

      $token = str_replace('Bearer ', '', $headers['Authorization']);

      $secret_key = "jwt2025";

      $decoded_data = JWT::decode($token, new Key($secret_key, 'HS256'));

      $user_obj->user_id = $decoded_data->data->id;

      $projects = $user_obj->get_user_all_projects();

      if ($projects->num_rows > 0) {

        $projects_arr = array();

        while ($row = $projects->fetch_assoc()) {

          $projects_arr[] = array(
            "id" => $row['id'],
            "name" => $row["name"],
            "description" => $row['description'],
            "user_id" => $row["user_id"],
            "status" => $row["status"],
            "created_at" => $row["created_at"]
          );
        }

        http_response_code(200); // Ok
        echo json_encode(array(
          "status" => 1,
          "projects" => $projects_arr
        ));
      } else {
        http_response_code(404); // no data found
        echo json_encode(array(
          "status" => 0,
          "message" => "No Projects found"
        ));
      }
    } catch (Exception $ex) {
      http_response_code(500); // no data found
      echo json_encode(array(
        "status" => 0,
        "message" => 'Error verifying token: ' . $ex->getMessage(),
      ));
    }
  } else {
    http_response_code(404);
    echo json_encode(array(
      "status" => 0,
      "message" => "Authorization header not found",
    ));
  }
} else {
  http_response_code(404);
  echo json_encode(array(
    "status" => 0,
    "message" => "Post method is not allowed",
  ));
}
