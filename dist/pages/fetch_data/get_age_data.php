<?php
header('Content-Type: application/json');
include '../../../database/conn.php';

$source = $_GET['source'] ?? 'pre';

$ageGroups = [
  'Infant' => 0,
  'Child' => 0,
  'Teen' => 0,
  'Adult' => 0,
  'Senior' => 0,
];

if ($source === 'evac') {
  $ageQuery = "
    SELECT act.classification, COUNT(*) as total
    FROM evac_reg_table ert
    LEFT JOIN pre_reg_table prt ON ert.pre_reg_id = prt.pre_reg_id
    LEFT JOIN age_class_table act ON prt.age_class_id = act.age_class_id
    GROUP BY act.classification
  ";
} else {
  $ageQuery = "
    SELECT act.classification, COUNT(*) as total
    FROM pre_reg_table prt
    LEFT JOIN age_class_table act ON prt.age_class_id = act.age_class_id
    GROUP BY act.classification
  ";
}

$result = mysqli_query($conn, $ageQuery);
while ($row = mysqli_fetch_assoc($result)) {
  $label = $row['classification'];
  $count = (int)$row['total'];
  $ageGroups[$label] = $count;
}

echo json_encode($ageGroups);
