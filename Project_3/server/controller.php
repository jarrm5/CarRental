<?php

include "connection.php";
include "utility.php";

session_start(); //start the session
$result = "";

if (isset($_POST['type']) && is_session_active()) {
    session_regenerate_id(); //regenerate the session to prevent fixation
    $_SESSION["start"] = time(); //reset the session start time
    $request_type = sanitizeMYSQL($connection, $_POST['type']);
    
    //$request_type = "return_car";
    //$_SESSION["username"] = "s.yusif";
    //$search = "nissan white";
    //$_POST['id'] = '4';

    switch ($request_type) { //check the request type
        case "show_searched_cars":
            $result = get_searched_cars($connection,sanitizeMYSQL($connection,$_POST['search']));
            break;
        case "rent_car":
            $result = rent_car($connection,sanitizeMYSQL($connection,$_POST['id']));
            break;
        case "show_rented_cars":
            $result = get_rented_cars($connection);
            break;
        case "return_car":
            $result = return_car($connection,sanitizeMYSQL($connection,$_POST['id']));
            break;
        case "show_returned_cars":
            $result = get_returned_cars($connection);
            break;
        case "login_name":
            $result = get_login_name($connection);
            break;
        case "logout":
            logout();
            $result= "success";
            break;
    }
}

echo $result;

function is_session_active() {
    return isset($_SESSION) && count($_SESSION) > 0 && time() < $_SESSION['start'] + 60 * 5; //check if it has been 5 minutes
}

function get_searched_cars($connection,$search){
    
    $criterion = explode(' ',$search);
    $cars = array();
    
    //Grab the data, put into associateive array.
    //Using the carID as the key will ensure that we will have unique cars in the end.
    foreach($criterion as $value) {
        $query = "SELECT car.picture, car.picture_type, carspecs.Make, carspecs.Model, carspecs.YearMade, car.Color, carspecs.Size, car.ID FROM car
                    INNER JOIN carspecs ON car.carspecsID = carspecs.ID
                    WHERE car.status = '1' AND (car.color = '" . $value . "' OR carspecs.Make = '" . $value . "' OR carspecs.Model = '" . $value . "' OR carspecs.Size = '" . $value . "' OR carspecs.YearMade = '" . $value . "');";
        
        $result = mysqli_query($connection, $query);
        
        if (!$result)
            return json_encode($cars);
        else {
            $row_count = mysqli_num_rows($result);
            for ($i = 0; $i < $row_count; $i++) {
                $row = mysqli_fetch_array($result);
                $cars[$row["ID"]] = $row;
            }
        }
    }
    
    $final = array();
    $final["searched_car"] = array();
    
    //Now build the HTML using the unique car data
    //Make a new associative array with the keys that match the script template
    foreach ($cars as $key => $value) {
        $array["picture"] = 'data:' . $value["picture_type"] . ';base64,' . base64_encode($value["picture"]);
        $array["make"] = $value["Make"];
        $array["model"] = $value["Model"];
        $array["year"] = $value["YearMade"];
        $array["color"] = $value["Color"];
        $array["size"] = $value["Size"];
        $array["ID"] = $value["ID"];
        $final["searched_car"][] = $array;
    }
    return json_encode($final);
    
}

function rent_car($connection, $car_id){
    
    $todays_date=date_create("now");
    
    $query_1 = "INSERT INTO rental (rentDate,returnDate,status,CustomerID,carID)
                VALUES ('" . date_format($todays_date,"Y-m-d") . "',NULL,1,'" . $_SESSION["username"] . "',$car_id);";
    $result_1 = mysqli_query($connection, $query_1);
    
    $query_2 = "UPDATE car SET status='2' WHERE id = $car_id";
    $result_2 = mysqli_query($connection, $query_2);
        
    if($result_1 && $result_2)
        return "success";
    else
        return "fail";
}

function get_rented_cars($connection) {
    $final = array();
    $final["rented_car"] = array();
    
    $query = "SELECT car.picture, car.picture_type, carspecs.Make, carspecs.Model, carspecs.yearMade, carspecs.size, rental.ID,rental.rentDate FROM Customer
                INNER JOIN rental ON customer.ID = rental.customerID
                INNER JOIN car ON rental.carID = car.ID
                INNER JOIN carspecs ON car.carspecsID = carspecs.ID 
                WHERE customer.ID='" . $_SESSION["username"] . "' AND rental.status = '1'";
    
    $result = mysqli_query($connection, $query);
    if (!$result)
        return json_encode($array);
    else {
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array["picture"] = 'data:' . $row["picture_type"] . ';base64,' . base64_encode($row["picture"]);
            $array["make"] = $row["Make"];
            $array["model"] = $row["Model"];
            $array["year"] = $row["yearMade"];
            $array["size"] = $row["size"];
            $array["rental_ID"] = $row["ID"];
            $rent_date = date_create($row["rentDate"]);
            $array["rent_date"] = date_format($rent_date,"D, F j, Y");
            $final["rented_car"][] = $array;
        }
    }
    return json_encode($final);
}
function return_car($connection,$rental_id){
    
    $todays_date=date_create("now");
    
    //update the rental status
    $query_1 = "UPDATE rental SET status='2',returnDate = '" . date_format($todays_date,"Y-m-d") . "' WHERE ID = '" . $rental_id . "';";
    $result_1 = mysqli_query($connection, $query_1);
    if(!$result_1)
        return "fail";
    
    //update the carid using a selects and joins
    $this_car = "";
    $query_2 = "SELECT car.id FROM Rental
                    INNER JOIN car ON rental.carID = car.ID
                    INNER JOIN carspecs ON car.carspecsID = carspecs.ID
                    WHERE rental.id = '" . $rental_id . "'";
    $result_2 = mysqli_query($connection, $query_2);
    if(!$result_2)
        return "fail";
    else
        $row = mysqli_fetch_array($result_2);
        $this_car = $row["id"];
    
    $query_3 = "UPDATE car SET status='1' WHERE ID = '" . $this_car . "'";
    $result_3 = mysqli_query($connection, $query_3);
    if(!$result_3)
        return "fail";
    
    return "success";
}
function get_returned_cars($connection) {
    $final = array();
    $final["returned_car"] = array();
    
    $query = "SELECT car.picture, car.picture_type, carspecs.Make, carspecs.Model, carspecs.yearMade, carspecs.size, rental.ID,rental.returnDate FROM customer
                INNER JOIN rental ON customer.ID = rental.customerID
                INNER JOIN car ON rental.carID = car.ID
                INNER JOIN carspecs ON car.carspecsID = carspecs.ID  
                WHERE customer.ID='" . $_SESSION["username"] . "' AND rental.status = '2'";
    
    $result = mysqli_query($connection, $query);
    if (!$result)
        return json_encode($array);
    else {
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array["picture"] = 'data:' . $row["picture_type"] . ';base64,' . base64_encode($row["picture"]);
            $array["make"] = $row["Make"];
            $array["model"] = $row["Model"];
            $array["year"] = $row["yearMade"];
            $array["size"] = $row["size"];
            $array["rental_ID"] = $row["ID"];
            $return_date = date_create($row["returnDate"]);
            $array["return_date"] = date_format($return_date,"D, F j, Y");
            $final["returned_car"][] = $array;
        }
    }
    return json_encode($final);
}

function get_login_name($connection){
    $query = "SELECT Name FROM Customer WHERE ID='" . $_SESSION["username"] . "'";
    $result = mysqli_query($connection, $query);
    if($result){
        $name = mysqli_fetch_array($result)["Name"];
        return $name;
    }
}

function logout() {
    // Unset all of the session variables.
    $_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }

// Finally, destroy the session.
    session_destroy();
}