<?php

// app/Console/Commands/DebugMakeMethods.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use NFePHP\NFe\Make;

class DebugMakeMethods extends Command
{
    protected $signature = 'debug:make-methods';
    
    public function handle()
    {
        $nfe = new Make();
        $methods = get_class_methods($nfe);
        
        $this->info("Todos os métodos do Make:");
        foreach ($methods as $method) {
            $this->line("  {$method}");
        }
        
        return Command::SUCCESS;
    }
}