<?php
function getProjects($limit = null) {
    global $db;
    $sql = "SELECT * FROM projects ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT :limit";
    }
    $stmt = $db->prepare($sql);
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSkillsByCategory() {
    global $db;
    $stmt = $db->query("SELECT * FROM skills ORDER BY category, percentage DESC");
    $skills = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $skills[$row['category']][] = $row;
    }
    return $skills;
}

function saveMessage($name, $email, $message) {
    global $db;
    $stmt = $db->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $email, $message]);
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>