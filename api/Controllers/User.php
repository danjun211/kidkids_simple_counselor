<?php
require_once "../Config/Database.php";
require_once '../Models/UserModel.php';

session_start();
$conn = Database::getConnection();

$userModel = new UserModel($conn);


switch($_SERVER['REQUEST_METHOD']){
    case 'GET':
    break;
    case 'POST':
    if(isset($_POST['login'])){
        $id = $_POST['id'];
        $user = $userModel->getById($id);
        if($user == null){
            $_SESSION['message'] = 'User with this email already exists!';
            header("location: error.php");
            exit;
        } else {
            if ( password_verify($_POST['password'], $user->password) ) {
                $_SESSION['id'] = $user->user_id;
                $_SESSION['email'] = $user->email;
                $_SESSION['name'] = $user->name;
                $_SESSION['user_type'] = $user->user_type;
                $_SESSION['user_image'] = $user->user_pic;
                
                // This is how we'll know the user is logged in
                $_SESSION['logged_in'] = true;

                header("location: http://localhost/ksc/home");
            }
            else {
                $_SESSION['message'] = "You have entered wrong password, try again!";
                header("location: error.php");
            }
        }
        return;
    }
    if(isset($_POST['register'])){
        $id = $_POST['id'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $name = $_POST['name'];
        $email = $_POST['email'];

		$imgFile = $_FILES['image']['name'];
		$tmp_dir = $_FILES['image']['tmp_name'];
		$imgSize = $_FILES['image']['size'];

		if(empty($id)){
			$_SESSION['message'] = "Please Enter User ID.";
            header("location: http://localhost/ksc/signup");
            exit;
		}
		else if(empty($name)){
			$_SESSION['message'] = "Please Enter Your Name.";
            header("location: http://localhost/ksc/signup");
            exit;
		}
		else if(empty($imgFile)){
			$_SESSION['message'] = "Please Select Image File.";
            header("location: http://localhost/ksc/signup");
            exit;
		}
		else
		{
			$upload_dir = '../../user_images/'; // upload directory
	
			$imgExt = strtolower(pathinfo($imgFile,PATHINFO_EXTENSION)); // get image extension
		
			// valid image extensions
			$valid_extensions = array('jpeg', 'jpg', 'png', 'gif'); // valid extensions
		
			// rename uploading image
			$userpic = rand(1000,1000000).".".$imgExt;
				
			// allow valid image file formats
			if(in_array($imgExt, $valid_extensions)){			
				// Check file size '5MB'
				if($imgSize < 5000000)				{
					move_uploaded_file($tmp_dir,$upload_dir.$userpic);
				}
				else{
					$_SESSION['message'] = "Sorry, your file is too large.";
                    header("location: http://localhost/ksc/signup");
                    exit;
                }
			}
			else{
				$_SESSION['message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";		
                header("location: http://localhost/ksc/signup");
                exit;
            }
		}

        $hash = md5(rand(0,1000));

        if($userModel->getById($id) != null){
            $_SESSION['message'] = 'User with this email already exists!';
            header("location: http://localhost/ksc/signup");
            exit;
        }

        if($userModel->register($id, $password, $name, $email, $hash, $userpic)){
            echo json_encode([
                'success'=> true,
                'messages'=> "회원가입에 성공했습니다!"
            ]);
            header("location: http://localhost/ksc/home");
        } else {
            echo json_encode([
                'success'=> false,
                'messages'=> "회원가입에 실패했습니다!"
            ]);
        };
        return;
    };
}

?>