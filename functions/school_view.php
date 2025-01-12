<?php
    include_once('../functions/general.php');
    global $conn;

    function schoolList($current_page = 1, $sort_column = 'school_id', $sort_order = 'asc') {
        $user_role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : null);
        $fullAccess = ($user_role == 1);
        global $conn;
    
        $records_per_page = 15;
        $offset = ($current_page - 1) * $records_per_page;
    
        // Define valid columns for sorting
        $validColumns = ['school_name', 'address'];
        if (!in_array($sort_column, $validColumns)) {
            $sort_column = 'school_id';
        }
    
        $sort_order = strtolower($sort_order) === 'desc' ? 'desc' : 'asc';
    
        $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
        $filter = isset($_GET['filter']) ? $conn->real_escape_string($_GET['filter']) : '';
    
        // Build the search query
        $conditions = $search !== '' ? "WHERE school_name LIKE '%$search%' OR address LIKE '%$search%'" : "";
        if ($filter !== '' && $filter !== 'all') {
            $conditions .= $conditions === '' ? "WHERE sem_count = '$filter'" : " AND sem_count = '$filter'";
        }
    
        // Build the main query
        $displayQuery = "SELECT * FROM university $conditions
                         ORDER BY $sort_column $sort_order
                         LIMIT $records_per_page OFFSET $offset";
        $result = $conn->query($displayQuery);
    
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '   
                    <tr>
                        <td>'.$row["school_name"].'</td>
                        <td>'.$row["address"].'</td>
                        <td style="text-align: center;">'.$row["acad_year"].'</td>
                        <td style="text-align: center;">'.$row["sem_count"].'</td>
                        <td style="text-align: right;" class="wrap"> 
                            <div class="icon '.(!$fullAccess ? 'disabled' : '').'" style="opacity: '.(!$fullAccess ? '0.5' : '1').';">
                                <div class="tooltip '.(!$fullAccess ? 'disabled-tooltip' : '').'">Edit</div>
                                <span> <ion-icon name="create-outline" onclick="'.(!$fullAccess ? 'return false;' : 'openEdit(this)').'" 
                                    data-id="'.$row["school_id"].'" 
                                    data-name="'.$row["school_name"].'" 
                                    data-address="'.$row["address"].'" 
                                    data-sem="'.$row["sem_count"].'"></ion-icon> </span>
                            </div>

                            <div class="icon '.(!$fullAccess ? 'disabled' : '').'" style="opacity: '.(!$fullAccess ? '0.5' : '1').';">
                                <div class="tooltip '.(!$fullAccess ? 'disabled-tooltip' : '').'">Delete</div>
                                <span> <ion-icon name="trash-outline" onclick="'.(!$fullAccess ? 'return false;' : 'openDelete(this)').'" 
                                        type="university" 
                                        data-id="'.$row["school_id"].'"></ion-icon> </span>
                            </div>
                        </td>
                    </tr>
                ';
            }
        } else {
            echo "<tr><td colspan='20'>No results found</td></tr>";
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
            $conditions .= " AND (school_name LIKE '%$search%' OR address LIKE '%$search%')";
        }
    
        // Add filter conditions
        if ($filter !== '' && $filter !== 'all') {
            $conditions .= " AND sem_count = '$filter'";
        }
    
        // Final query to count the records
        $countQuery = "SELECT COUNT(*) as total FROM university WHERE $conditions";
        $result = $conn->query($countQuery);
        return $result->fetch_assoc()['total'];
    }
?>