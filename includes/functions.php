<?php
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
function calculateRentalCost($start, $end, $rates) {
    $startTime = strtotime($start);
    $endTime = strtotime($end);
    if (!$startTime || !$endTime || $endTime <= $startTime) {
        return ['error' => 'Invalid date range'];
    }
    $diffInSeconds = $endTime - $startTime;
    $totalHours = ceil($diffInSeconds / 3600);
    $weeks = floor($totalHours / 168); 
    $remainingHours = $totalHours % 168;
    $days = floor($remainingHours / 24);
    $hours = $remainingHours % 24;
    $cost = ($weeks * $rates['weekly']) + ($days * $rates['daily']) + ($hours * $rates['hourly']);
    return [
        'total_hours' => $totalHours,
        'breakdown' => [
            'weeks' => $weeks,
            'days' => $days,
            'hours' => $hours
        ],
        'total_cost' => number_format($cost, 2)
    ];
}
function renderStars($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = 5 - $fullStars - $halfStar;
    $html = '';
    for ($i = 0; $i < $fullStars; $i++) $html .= '<i class="fas fa-star text-warning"></i>';
    if ($halfStar) $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    for ($i = 0; $i < $emptyStars; $i++) $html .= '<i class="far fa-star text-warning"></i>';
    return $html;
}
?>
