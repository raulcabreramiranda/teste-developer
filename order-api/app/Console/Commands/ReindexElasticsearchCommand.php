<?php

namespace App\Console\Commands;

use App\Order;
use Elasticsearch\Client;
use Illuminate\Console\Command;

class ReindexElasticsearchCommand extends Command
{
    protected $name = "search:reindex";
    protected $description = "Indexa todos os pedidos para elasticsearch";
    private $search;

    public function __construct(Client $search)
    {
        parent::__construct();

        $this->search = $search;
    }

    public function handle()
    {
        $this->info('Indexando todos os pedidos. Pode demorar um pouco.');

        foreach (Order::cursor() as $model)
        {
            $this->search->index([
                'index' => $model->getSearchIndex(),
                'type' => $model->getSearchType(),
                'id' => $model->id,
                'body' => $model->toSearchArray(),
            ]);

            $this->output->write('.');
        }

        $this->info("Feito !!!!!!!!");
    }
}
