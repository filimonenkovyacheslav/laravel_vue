<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use BaseParser;
use Parser;

class ParserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 1;
    public $timeout = 1200000;

    protected $parserId;
    protected $job;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($parserId)
    {
        $this->parserId = $parserId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(0);
        BaseParser::doParse($this->parserId, $this->job);
    }

    public function failed()
    {
        BaseParser::setParserId($this->parserId);
        $parser = Parser::getParser($this->parserId);
        if(!$parser) return;
        if($parser->status != 0) {
            //BaseParser::setError('Abnormal shutdown');
        }
    }
}
