<?php

require_once 'services/SeedService.php';


class SeedController 
{

    public static function seed()
    {
        $seedService = new SeedService();
        $seedService->seedDatabase();
    }

}


