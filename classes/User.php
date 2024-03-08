<?php

require_once "Database.php";

class User extends Database
{
    public function store($request) {
       $first_name = $request['first_name'];
       $last_name = $request['last_name'];
       $username = $request['username'];
       $password = $request['password'];

       $password = password_hash($password, PASSWORD_DEFAULT);
       //hash化

       $sql = "INSERT INTO `users`(`first_name`, `last_name`, `username`, `password`) VALUES ('$first_name', '$last_name', '$username', '$password')";

       if($this->conn->query($sql)){
          header('location: ../views');  //go to index
          exit;                          // same as die
       } else {
          die('Error creating the user: ' . $this->conn->error);
       }
    }

    public function login ($request)
    {
      $username = $request['username'];
      $password = $request['password'];

      $sql = "SELECT * FROM users WHERE username = '$username'";

      $result = $this->conn->query($sql);

      #Check the username
      if($result->num_rows == 1){
         #Check if the password is correct
         $user = $result->fetch_assoc();
        

         if(password_verify($password, $user['password'])){
            #Create session veriables for future use.
            session_start();
            $_SESSION['id']        =  $user['id'];
            $_SESSION['username']  =  $user['username'];
            $_SESSION['full_name'] =  $user['first_name'] . " " . $user['last_name'];

            header('location: ../views/dashboard.php');
            exit;
         } else {
            die('password is incorrect');
         }
      } else {
         die('username not found.');
      }
    }

    public function logout()
    {
      session_start();
      session_unset();
      session_destroy();

      header('location: ../views');
      exit;
    }

    public function getAllUsers()
    {
      $sql = "SELECT id, first_name, last_name, username, photo FROM users";

      if($result = $this->conn->query($sql)){
         return $result;
      } else {
         die("Error retrieving all user: " . $this->conn->error);
      }
    }

    public function getUser($id)
    {
      $sql = "SELECT * FROM users WHERE id = $id";

      if($result = $this->conn->query($sql)){
         return $result->fetch_assoc();
      } else {
         die('Error retrieving the user: ' . $this->conn->error);
      }
    }

    public function update($request, $files){
      session_start();
      $id             = $_SESSION['id'];
      $first_name = $request['first_name'];
      $last_name = $request['last_name'];
      $username = $request['username'];
      $photo = $files['photo']['name'];//name = property  $photo = jpeg-1
      $tmp_photo = $files['photo']['tmp_name'];//tmp_photo = tempolary(transfer location)  tmp_name = property

      $sql = "UPDATE users SET `first_name`='$first_name', `last_name` = '$last_name', `username` = '$username' WHERE id  =$id";

      if($this->conn->query($sql)){
         $_SESSION['username']  =$username;
         $_SESSION['full_name'] = "$first_name $last_name";
         
         #if there is am uploaded photo, save it to the db and save the file to images folder.
         if($photo){
            $sql = "UPDATE users SET photo = '$photo' WHERE id = $id";
            $destination = "../assets/images/$photo";

            //Save the image name to db
            if($this->conn->query($sql)){
               //Save the file to images folder
               if(move_uploaded_file($tmp_photo, $destination)){ 
                  header('location: ../views/dashboard.php');
                  exit;
               } else {
                  die('Error moving the photo.');
               } 
            }
            else {
               die('Error uploading the photo: ' . $this->conn->error);
            }
 
               header('location: ../views/dashboard.php');
              exit;
            } else {
              die('Error updating the user: ' . $this->conn->error);
            } 
         }
    }

    public function delete()
        {
          session_start();
          $id = $_SESSION['id'];

          $sql = "DELETE FROM users WHERE id = $id";

          if($this->conn->query($sql)){
             $this->logout();
          } else {
            die('Error deleting your account: ' . $this->conn->error);
          }
        }

}
?>