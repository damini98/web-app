<?php
function flashMsg(){
    if (isset($_SESSION['error'])){
        echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])){
        echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
        unset($_SESSION['success']);
    }
}

function validatePos() {
    for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['year'.$i]) ) continue;
      if ( ! isset($_POST['desc'.$i]) ) continue;
  
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];
  
      if ( strlen($year) == 0 || strlen($desc) == 0 ) {
        return "All fields are required";
      }
  
      if ( ! is_numeric($year) ) {
        return "Position year must be numeric";
      }
    }
    return true;
}

function loadPos($pdo, $profile_id){
    $stmt = $pdo->prepare('select * from position where profile_id = :prof order by rank' );
    $stmt->execute(array(':prof' => $profile_id));
    $positions = array();
    //$positions = $stmt->fetchAll(PDO::FETCH_ASSOC); return $positions;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $positions[] = $row;
    }
    return $positions;
}

function loadEdu($pdo, $profile_id){
  $stmt = $pdo->prepare('select year, name from education join institution on education.institution_id = institution.institution_id where profile_id = :prof order by rank' );
  $stmt->execute(array(':prof' => $profile_id));
  $educations = array();
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
      $educations[] = $row;
  }
  return $educations;
}
