<?php
/**
 * rank.php
 * Central rank system for users
 * Place in: /src/ajax/community/rank.php
 */
if (!function_exists("getRank")) {
    /**
     * Get user rank based on points and admin status
     * 
     * @param int  $points
     * @param bool $isAdmin
     * @return array [rankName, badgeClass, iconPath, requiredPoints, minPoints, maxPoints]
     */
    function getRank($points, $isAdmin = false) {
        // If admin, override rank
        if ($isAdmin) {
            return [
                "Admin",              
                "level-b",            
                "https://animex.free.nf/public/images/level-b.png", 
                0,                    
                0,                    
                PHP_INT_MAX           
            ];
        }
        // Normal user ranks (no "New")
        $ranks = [
            [
                "name"         => "Angelfish",
                "badgeClass"   => "level-a",
                "icon"         => "https://animex.free.nf/public/images/level-a.png",
                "required"     => 60000,
                "minPoints"    => 60000,
                "maxPoints"    => 119999
            ],
            [
                "name"         => "Crab",
                "badgeClass"   => "level-a up-2",
                "icon"         => "https://animex.free.nf/public/images/level-a-2.png",
                "required"     => 120000,
                "minPoints"    => 120000,
                "maxPoints"    => 179999
            ],
            [
                "name"         => "Starfish",
                "badgeClass"   => "level-a up-3",
                "icon"         => "https://animex.free.nf/public/images/level-a-3.png",
                "required"     => 180000,
                "minPoints"    => 180000,
                "maxPoints"    => 239999
            ],
            [
                "name"         => "Dolphin",
                "badgeClass"   => "level-a up-4",
                "icon"         => "https://animex.free.nf/public/images/level-a-4.png",
                "required"     => 240000,
                "minPoints"    => 240000,
                "maxPoints"    => PHP_INT_MAX
            ],
        ];
        // Loop and find rank
        foreach ($ranks as $rank) {
            if ($points >= $rank["minPoints"] && $points <= $rank["maxPoints"]) {
                return [
                    $rank["name"],         
                    $rank["badgeClass"],   
                    $rank["icon"],         
                    $rank["required"],     
                    $rank["minPoints"],    
                    $rank["maxPoints"]     
                ];
            }
        }
        // fallback → no rank (for users under 60k points)
        return ["", "", "", 0, 0, 59999];
    }
}
