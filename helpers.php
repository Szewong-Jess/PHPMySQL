<?php

//Wong Sze 300359901

function display_form($errors=[]){
    echo "<form method='POST' action='$_SERVER[PHP_SELF]'>
    <label>PID</label><input type='number' name='pid' value='" . ($_SESSION['PID'] ?? '') . "'/>";

if (isset($errors[0])) {
echo "<span class='err'>$errors[0]</span>";
}

echo "<br/><label>Name</label><input type='text' name='name' value='" . ($_SESSION['Name'] ?? '') . "'/>";

if (isset($errors[1])) {
echo "<span class='err'>$errors[1]</span>";
}

echo "<br/><label>Team Name</label><select name='teams'>
    <option value='U10' " . (($_SESSION['Team Name'] ?? '') == 'U10' ? 'Selected' : '') . ">U10</option>
    <option value='U11' " . (($_SESSION['Team Name'] ?? '') == 'U11' ? 'Selected' : '') . ">U11</option>
    <option value='U12' " . (($_SESSION['Team Name'] ?? '') == 'U12' ? 'Selected' : '') . ">U12</option>
    </select>";

if (isset($errors[2])) {
echo "<span class='err'>$errors[2]</span>";
}
echo "<br/><label>Gender</label>
    <input type='radio' name='gender' value='M' " . (($_SESSION['Gender'] ?? '') == 'M' ? 'Checked' : '') . "/><label>Male</label>
    <input type='radio' name='gender' value='F' " . (($_SESSION['Gender'] ?? '') == 'F' ? 'Checked' : '') . "/><label>Female</label>
    <input type='radio' name='gender' value='F' " . (($_SESSION['Gender'] ?? '') == 'X' ? 'Checked' : '') . "/><label>Others</label>";

if (isset($errors[3])) {
echo "<span class='err'>$errors[3]</span>";
}

echo "<br/><label>Favorite Sports</label><br/>
    <input type='checkbox' name='sports[]' value='sc' " . (in_array('sc', ($_SESSION['Favorite Sports'] ?? [])) ? 'Checked' : '') . "/> <label>Soccer</label><br />
    <input type='checkbox' name='sports[]' value='ts' " . (in_array('ts', ($_SESSION['Favorite Sports'] ?? [])) ? 'Checked' : '') . "/> <label>Tennis</label><br />
    <input type='checkbox' name='sports[]' value='sw' " . (in_array('sw', ($_SESSION['Favorite Sports'] ?? [])) ? 'Checked' : '') . "/> <label>Swimming</label><br />
    <input type='checkbox' name='sports[]' value='bb' " . (in_array('bb', ($_SESSION['Favorite Sports'] ?? [])) ? 'Checked' : '') . "/> <label>Basketball</label><br />";

    if (isset($errors[4])) {
echo "<span class='err'>$errors[4]</span>";
}

echo "<br/><input type='submit' name='submit' value='Submit'/>
</form>";
}

function confirm_form() {
    echo "<h1>Confirm Form Data</h1>";
    echo "<form method='POST' action='$_SERVER[PHP_SELF]'>";

    foreach ($_SESSION as $key => $value) {
        echo "<p><strong>$key:</strong> ";
        if (is_array($value)) {
            echo implode(", ", $value);
        } else {
            echo $value;
        }
        echo "</p>";
    }

    echo "<input type='submit' name='submit' value='Confirm'/>";
    echo "<input type='submit' name='submit' value='Edit'/>";
    echo "</form>";
}

function process_form(){

    try {
        $conn = new PDO("mysql:host=localhost", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "<p class='err'>Error: " . $e->getMessage() . "</p>";
    }

    try {
        $sql = "CREATE DATABASE IF NOT EXISTS players_db;
                USE players_db;
                CREATE TABLE IF NOT EXISTS Player (
                    PID INT PRIMARY KEY,
                    PName VARCHAR(20),
                    TeamName CHAR(3),
                    Gender CHAR(1)
                );
                CREATE TABLE IF NOT EXISTS Player_FavSports (
                    PID INT,
                    FavSport CHAR(2),
                    PRIMARY KEY(PID, FavSport),
                    FOREIGN KEY(PID) REFERENCES Player(PID)
                );";
        $conn->exec($sql);
    } catch (PDOException $e) {
        echo "<p class='err'>Error: " . $e->getMessage() . "</p>";
    }

    try {
        $insert = "INSERT INTO players_db.Player VALUES (?,?,?,?);";
        $stmt = $conn->prepare($insert);
        $stmt->execute(array($_SESSION['PID'], $_SESSION['Name'], $_SESSION['Team Name'], $_SESSION['Gender']));
        
        foreach ($_SESSION['Favorite Sports'] as $sport) {
            $insert = "INSERT INTO players_db.Player_FavSports VALUES (?,?);";
            $stmt = $conn->prepare($insert);
            $stmt->execute(array($_SESSION['PID'], $sport));
        }

        echo "<p style='color:green'>Data Inserted Successfully.</p>";
    } catch (PDOException $e) {
        echo "<p class='err'>Error: " . $e->getMessage() . "</p>";
    }

}

function validate_form(){
    $errors = array(); // Initialize empty array for errors
    // Validate PID
    $pid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT); // Use filter_input to validate PID as positive integer
    if ($pid === false || $pid < 1) {
        $errors[0] = "PID must be a positive integer."; // Set error message if validation fails
    } else {
        $_SESSION['PID'] = $pid; // Set user input as value in $_SESSION array if validation passes
    }

    // Validate Name
    $name = trim($_POST['name']); // Get and trim user input for Name
    if (empty($name)) {
        $errors[1] = "Name must not be empty."; // Set error message if validation fails
    } else {
        $_SESSION['Name'] = $name; // Set user input as value in $_SESSION array if validation passes
    }

    // Validate Team Name
    $validTeams = array('U10', 'U11', 'U12'); // Define array of valid team names
    $team = $_POST['teams']; // Get user input for Team Name
    if (!in_array($team, $validTeams)) {
        $errors[2] = "Invalid Team Name."; // Set error message if validation fails
    } else {
        $_SESSION['Team Name'] = $team; // Set user input as value in $_SESSION array if validation passes
    }

    // Validate Gender
    $validGenders = array('F', 'M', 'X'); // Define array of valid genders
    $gender = $_POST['gender']; // Get user input for Gender
    if (!in_array($gender, $validGenders)) {
        $errors[3] = "Invalid Gender."; // Set error message if validation fails
    } else {
        $_SESSION['Gender'] = $gender; // Set user input as value in $_SESSION array if validation passes
    }

    // Validate Favorite Sports
    if (isset($_POST['sports'])) { // Check if user has selected any sports
        $selectedSports = $_POST['sports']; // Get user input for Favorite Sports
        $validSports = array('sc','ts','sw','bb'); // Define array of valid sports
        foreach ($selectedSports as $sport) {
            if (!in_array($sport, $validSports)) {
                $errors[4] = "Invalid Favorite Sports."; // Set error message if validation fails
                break; // Break out of loop if any invalid sport is found
            }
        }
        $_SESSION['Favorite Sports'] = $selectedSports; // Set user input as value in $_SESSION array if validation passes
    }
    return $errors; // Return array of errors
}

function display_HTML_header($title = '') {
    echo <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>{$title}</title>
    </head>
    <body>
HTML;
}

function display_HTML_footer(){
    echo <<<HTML
    </body>
    </html>
    HTML;
}
?>