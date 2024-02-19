<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGrabTheLabRoundTable extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table("grabthelab_rounds");

        $tbl->addColumn("proposal_start", "datetime", ["null" => false])
            ->addColumn("proposal_end", "datetime", ["null" => false])
            ->addColumn("max_proposals", "integer", ["null" => true, "default" => null]);

        $tbl->create();
    }
}
