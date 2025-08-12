<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:action {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Action class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $className = $this->argument('name');
        $classContent = '<?php' . PHP_EOL . 'class ' . $className . ' { }' . PHP_EOL;

        $filePath = app_path('Actions/' . $className . '.php');

        file_put_contents($filePath, $classContent);

        $this->info($className . ' class generated successfully.');
    }
}
