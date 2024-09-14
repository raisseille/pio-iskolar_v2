<?php
    include_once('../functions/general.php');
    global $conn;

// Status Update (for every viewing)
    function updateStatus() {
        global $conn;
        $currentDate = date('Y-m-d');
        // Update status to ACTIVE where current date is between st_date and end_date
        $updateActive = "UPDATE announcements 
                        SET _status = 'ACTIVE' 
                        WHERE st_date <= '$currentDate' AND end_date >= '$currentDate'";
        $conn->query($updateActive);

        // Update status to INACTIVE where current date is not between st_date and end_date
        $updateInactive = "UPDATE announcements 
                        SET _status = 'INACTIVE' 
                        WHERE st_date > '$currentDate' OR end_date < '$currentDate'";
        $conn->query($updateInactive);
    }

    function annTitle() {

    }

// Admin View
function annList($current_page = 1, $sort_column = 'title', $sort_order = 'asc') {
    global $conn;

    // Update the status of the announcements before fetching them
    updateStatus();

    $records_per_page = 15;
    $offset = ($current_page - 1) * $records_per_page;

    // Define valid columns for sorting
    $validColumns = ['batch_no', 'title', '_status', 'st_date', 'end_date'];
    if (!in_array($sort_column, $validColumns)) {
        $sort_column = 'st_date';
    }

    $sort_order = strtolower($sort_order) === 'desc' ? 'desc' : 'asc';
    $sort_order .= ", announce_id DESC";

    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $filter = isset($_GET['filter']) ? $conn->real_escape_string($_GET['filter']) : '';

    // Build the search query
    $conditions = $search !== '' ? "WHERE title LIKE '%$search%' OR _status LIKE '%$search%' OR st_date LIKE '%$search%' OR end_date LIKE '%$search%'" : "";
    if ($filter !== '' && $filter !== 'all') {
        $conditions .= $conditions === '' ? "WHERE status = '$filter'" : " AND status = '$filter'";
    }

    // Build the main query
    $displayQuery = "SELECT * FROM announcements $conditions
                     ORDER BY $sort_column $sort_order
                     LIMIT $records_per_page OFFSET $offset";
    $result = $conn->query($displayQuery);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $style = match ($row["_status"]) {
                "ACTIVE" => "color: rgb(0, 136, 0); font-weight: 600;",
                "INACTIVE" => "color: rgb(189, 0, 0); font-weight: 600;",
                default => "",
            };

            // Display "ALL" if batch_no is 0, otherwise display the batch_no
            $batchNoDisplay = $row["batch_no"] == 0 ? "ALL" : $row["batch_no"];

            echo '   
                <tr>
                    <td><input type="checkbox" name="selected_rows[]"></td> 
                    <td style="text-align: center;">'.$batchNoDisplay.'</td>
                    <td>'.$row["title"].'</td>
                    <td>'.$row["st_date"].'</td>
                    <td>'.$row["end_date"].'</td>
                    <td style="'.$style.'; text-align: center;">'.$row["_status"].'</td>
                    <td style="text-align: right;" class="wrap"> 
                        <div class="icon">
                            <div class="tooltip"> View</div>
                            <span> <ion-icon name="eye-outline" onclick="openPrev(this)" 
                                data-id="'.$row["announce_id"].'" 
                                data-title="'.$row["title"].'" 
                                data-status="'.$row["_status"].'" 
                                data-st_date="'.$row["st_date"].'" 
                                data-end_date="'.$row["end_date"].'" 
                                data-img="'.$row["img_name"].'" 
                                data-content="'.$row["content"].'"></ion-icon> </span>
                        </div>
                        
                        <div class="icon">
                            <div class="tooltip"> Edit</div>
                            <span> <ion-icon name="create-outline" onclick="openEdit(this)" 
                                data-id="'.$row["announce_id"].'" 
                                data-title="'.$row["title"].'" 
                                data-status="'.$row["_status"].'" 
                                data-st_date="'.$row["st_date"].'" 
                                data-end_date="'.$row["end_date"].'"
                                data-content="'.$row["content"].'"></ion-icon> </span>
                        </div>

                        <div class="icon">
                            <div class="tooltip"> Delete</div>
                            <span> <ion-icon name="trash-outline" onclick="openDelete(this)" 
                                data-id="'.$row["announce_id"].'" 
                                data-img="'.$row["img_name"].'"></ion-icon> </span>
                        </div>
                    </td>
                </tr>
            ';
        }
    } else {
        echo "<tr><td colspan='20'>No announcements found</td></tr>";
    }
}


    function getTotalRecords() {
        global $conn;
    
        $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
        $filter = isset($_GET['filter']) ? $conn->real_escape_string($_GET['filter']) : '';
    
        // Base condition to ensure WHERE clause is valid
        $conditions = "1=1";
    
        // Add search conditions
        if ($search !== '') {
            $conditions .= " AND (title LIKE '%$search%' OR _status LIKE '%$search%' OR st_date LIKE '%$search%' OR end_date LIKE '%$search%')";
        }
    
        // Add filter conditions
        if ($filter !== '' && $filter !== 'all') {
            $conditions .= " AND _status = '$filter'";
        }
    
        // Final query to count the records
        $countQuery = "SELECT COUNT(*) as total FROM announcements WHERE $conditions";
        $result = $conn->query($countQuery);
        return $result->fetch_assoc()['total'];
    }    

// Dashboard Announcements
    // For Scholars -- add eval logic later
    function annDisplay() {
        global $conn;
        $batch_no = $_SESSION['bid'];
        $display = "SELECT img_name, title, content, _status, st_date 
                    FROM announcements 
                    WHERE _status = 'ACTIVE' 
                    AND (batch_no = 0 OR batch_no = $batch_no) 
                    ORDER BY st_date DESC, announce_id DESC";
        $result = $conn->query($display);

        if ($result->num_rows > 0) {
            $hr = false;
            while ($row = $result->fetch_assoc()) {
                print '
                    <div class="title">'.$row["title"].'</div>
                    <div class="titleDate">'.$row["st_date"].'</div>
                    <div class="info-box">
                        <img src="../assets/'.$row["img_name"].'">
                        <p class="message">'.nl2br($row["content"]).'</p>
                    </div> <br> <br>
                ';
            }
        }
    }

// Front Page Announcements
    function annFront() {
        global $conn;
        $display = "SELECT img_name, title, content, _status FROM announcements WHERE _status = 'ACTIVE' AND batch_no = 0 ORDER BY st_date DESC, announce_id DESC";
        $result = $conn->query($display);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                print '
                    <div class="mySlides fade">
                        <div class="imgContainer">
                            <img src="../assets/'.$row["img_name"].'">
                        </div>
                        <div class="text">
                            <h2> '.$row["title"].' </h2> <hr>
                            <p>'.nl2br($row["content"]).' </p> 
                        </div>
                    </div>
                ';
            }
        }
    }

    function slideshowButtons() {
        global $conn;
        $display = "SELECT COUNT(_status) as count FROM announcements WHERE _status = 'ACTIVE'"; // Using alias to retrieve count as 'count'
        $result = $conn->query($display);
        if ($result->num_rows > 0) {
            $count = $result->fetch_assoc()['count']; // Fetching the count directly
            for ($i = 0; $i < $count; $i++) {
                print '<span class="dot" onclick="currentSlide('.($i+1).')"></span>';
            }
        }

    }

?>