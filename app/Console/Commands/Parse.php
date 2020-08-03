<?php

namespace App\Console\Commands;

use App\Parser\Parser;
use App\Parser\ParserShell;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Parse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Markdown 2 graceful bbcode 4 mcbbs';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('input',InputArgument::OPTIONAL,'Input markdown file. set "-" to use stdin.',base_path('README.md'));
        $this->addArgument('output',InputArgument::OPTIONAL,'Output bbcodde file. set "-" to use stdout.','-');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ParserShell::init([
            __FILE__,
            $this->input->getArgument('input'),
            $this->input->getArgument('output'),
        ]);
    }
}
