<?php

require '../vendor/autoload.php';

use Firebase\JWT\JWT;

use Firebase\JWT\ExpiredException;

use Firebase\JWT\SignatureInvalidException;

use Firebase\JWT\BeforeValidException;

use Firebase\JWT\Key;

ini_set("display_errors", 1);

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

$secret_key = "jwt2025";

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === "GET") {

  if (isset($headers['Authorization'])) {
    try {

      $token = str_replace('Bearer ', '', $headers['Authorization']);

      $decoded_data = JWT::decode($token, new Key($secret_key, 'HS256'));

      $projects = $user_obj->get_all_projects();

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
          "projects" => $projects_arr,
        ));
      } else {
        http_response_code(404); // no data found
        echo json_encode(array(
          "status" => 0,
          "message" => "No Projects found"
        ));
      }
    } catch (ExpiredException $e) {
      http_response_code(404); // not found
      echo json_encode(array(
        "status" => 0,
        "message" => 'JWT Token has expired: ' . $e->getMessage(),
      ));
    } catch (SignatureInvalidException $e) {
      http_response_code(404); // not found
      echo json_encode(array(
        "status" => 0,
        "message" => 'Signature verification failed: ' . $e->getMessage(),
      ));
    } catch (BeforeValidException $e) {
      http_response_code(404); // not found
      echo json_encode(array(
        "status" => 0,
        "message" => 'JWT Token not valid yet: ' . $e->getMessage(),
      ));
    } catch (UnexpectedValueException $e) {
      http_response_code(404); // not found
      echo json_encode(array(
        "status" => 0,
        "message" => 'Unexpected value: ' . $e->getMessage(),
      ));
    } catch (Exception $e) {
      http_response_code(404); // not found
      echo json_encode(array(
        "status" => 0,
        "message" => 'Error verifying token: ' . $e->getMessage(),
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
