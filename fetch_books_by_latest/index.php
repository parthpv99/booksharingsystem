<?php

// PHP code to Fetch All the Latest Book from the Database and send it as JSON to Front-End

require "../DBConnection.php";
require "../transaction_notifications.php";
header("Content-Type: application/json; charset=UTF-8");

$fp = fopen('php://input', 'r');
$raw = stream_get_contents($fp);
$data = json_decode($raw);
$final;

session_start();

// email_id
// no_of_books

if(!empty($_SESSION)) 
{
    if (!empty($_SESSION['user_email']) && !empty($data)) 
    {
        try 
        {
            $stmt_id = $conn->prepare("SELECT * FROM book_details WHERE is_approved = 1 AND owner_id IN (SELECT user_id FROM user_details WHERE activated_user = 1) ORDER BY added_date DESC LIMIT $data->no_of_books");
            $stmt_id->execute();
    
            $row['no_records'] = $stmt_id->rowCount();
            $row['is_error'] = false;
            $row['message'] = "Fetched books sucessfully";
            
            for ($i=0; $i< $stmt_id->rowCount(); $i++) 
            {
                $row_id = $stmt_id->fetch(PDO::FETCH_ASSOC);
                
                $temp =  $row_id['owner_id'];
                $comment = $conn->prepare("SELECT * FROM user_details WHERE user_id = $temp");
                $comment->execute();
                $r = $comment->fetch(PDO::FETCH_ASSOC);
                $file_dir='https://booksharingsystem.000webhostapp.com/images/' . $r['email_id'] . '/' . $row_id['book_id'];
                
                if(!empty($row_id['photo_path1']))
                {
                    $path1 = $row_id['photo_path1'];
                    // echo $path1;
                    // echo $file_dir;
                    $row_id['photo_path1'] = $file_dir . '/' . $path1;
                }
                if(!empty($row_id['photo_path2']))
                {
                    $path2 = $row_id['photo_path2'];
                    $row_id['photo_path2'] = $file_dir . '/' . $path2;
                }
                if(!empty($row_id['photo_path3']))
                {
                    $path3 = $row_id['photo_path3'];
                    $row_id['photo_path3'] = $file_dir . '/' . $path3;
                }
                if(!empty($row_id['photo_path4']))
                {
                    $path4 = $row_id['photo_path4'];
                    $row_id['photo_path4'] = $file_dir . '/' . $path4;
                }
                
                
                array_push($row, $row_id);
            }
            
            $final = $row;
        } 
        catch(PDOException $e) 
        {
            $final['is_error'] = true;
            $final['message'] = $e->getMessage();
        }
        catch(Exception $e) 
        {
            $final['is_error'] = true;
            $final['message'] = $e->getMessage();
        } 
    } 
    else 
    {
        $final = array("is_error"=>true, "message"=>"Values not inserted properly");
    }
} 
else 
{
    $final = array("is_error"=>true, "message"=>"Please Sign In");
}

echo json_encode($final);

?>