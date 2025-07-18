<?php
include '../../../database/conn.php';
header('Content-Type: application/json');

if (!isset($_GET['evac_loc_id']) || $_GET['evac_loc_id'] === '') {
    echo json_encode(['success' => false, 'message' => 'No location provided']);
    exit;
}

$evacLocId = $_GET['evac_loc_id'];

/* ─────────────────────────────
   1.  Summary info for location
   ───────────────────────────── */
$infoSql = "
  SELECT 
  el.name                    AS location_name,
  COUNT(r.room_id)           AS total_rooms,
  SUM(r.room_capacity)       AS total_capacity,
  MAX(r.room_capacity)       AS max_capacity_per_room
FROM evac_loc_table el
LEFT JOIN room_table r ON r.evac_loc_id = el.evac_loc_id
WHERE el.evac_loc_id = ?
GROUP BY el.evac_loc_id;

";
$infoStmt = $conn->prepare($infoSql);
$infoStmt->bind_param('s', $evacLocId);
$infoStmt->execute();
$infoRow = $infoStmt->get_result()->fetch_assoc();

/* ─────────────────────────────
   2.  Detailed room list
   ───────────────────────────── */
$roomsSql = "
  SELECT
      r.room_id,
      r.room_name,
      r.room_capacity,
      COUNT(er.pre_reg_id)                   AS number_of_people,
      (r.room_capacity - COUNT(er.pre_reg_id)) AS remaining_slots,
      CASE
          WHEN r.room_capacity - COUNT(er.pre_reg_id) > 0 THEN 1
          ELSE 0
      END AS is_available
  FROM room_table r
  LEFT JOIN evac_reg_table er ON er.room_id = r.room_id
  WHERE r.evac_loc_id = ?
  GROUP BY r.room_id
  ORDER BY r.room_name
";
$roomsStmt = $conn->prepare($roomsSql);
$roomsStmt->bind_param('s', $evacLocId);
$roomsStmt->execute();
$roomsRes = $roomsStmt->get_result();

$rooms = [];
while ($row = $roomsRes->fetch_assoc()) {
    $rooms[] = [
        'name'            => $row['room_name'],
        'capacity'        => (int) $row['room_capacity'],
        'occupied'        => (int) $row['number_of_people'],
        'remaining'       => (int) $row['remaining_slots'],
        'is_available'    => (bool) $row['is_available']
    ];
}


/* ─────────────────────────────
   3.  Output JSON
   ───────────────────────────── */
if ($infoRow) {
    echo json_encode([
        'success'          => true,
        'location'         => $infoRow['location_name'],
        'total_rooms'      => (int) $infoRow['total_rooms'],
        'available_rooms'  => (int) $infoRow['available_rooms'],
        'capacity_per_room'=> (int) $infoRow['capacity_per_room'],
        'rooms'            => $rooms
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Location not found']);
}
?>
