<?php

namespace App\Model;

use Nette;

class GrabTheLabModel extends BaseModel {

    public $implicitTable = 'grabthelab_project';

    public function __construct(Nette\Database\Context $database, Nette\DI\Container $c) {
        parent::__construct($database, $c);
    }

    public function getRounds() {
        return $this->table("grabthelab_rounds");
    }

    public function getActiveRound() {

        $now = new \DateTime();

        return $this->getRounds()
            ->where("proposal_start < ?", $now)
            ->where("proposal_end > ?", $now)
            ->fetch();
    }

    public function updateRound($id, $from, $to, $max_proposals) {
        $this->table('grabthelab_rounds')->where('id', $id)->update([
            'proposal_start' => $from,
            'proposal_end' => $to,
            'max_proposals' => $max_proposals
        ]);
    }

    public function getUpcomingRound() {
        $now = new \DateTime();

        return $this->getRounds()
            ->where("proposal_start > ?", $now)
            ->fetch();
    }

    public function getPastRounds() {
        $now = new \DateTime();

        return $this->getRounds()
            ->where("proposal_end < ?", $now);
    }

    public function createRound($from, $to, $max_proposals) {
        $this->table('grabthelab_rounds')->insert([
            'proposal_start' => $from,
            'proposal_end' => $to,
            'max_proposals' => $max_proposals
        ]);
    }

    public function createProject($owner_id, $data) {
        $this->getTable()->insert([
            'users_id' => $owner_id,
            'data' => is_array($data) ? json_encode($data) : $data,
            'state' => 'draft'
        ]);
    }

    public function getProjectById($id) {
        return $this->getTable()->where('id', $id)->fetch();
    }

    public function getProjectDraft($owner_id) {
        return $this->getTable()->where('state', 'draft')->fetch();
    }

    public function getProjectProposed($owner_id) {
        return $this->getTable()->where('state', 'proposed')->fetch();
    }

    public function updateProject($id, $data) {
        $this->getTable()->where('id', $id)->update([
            'data' => is_array($data) ? json_encode($data) : $data
        ]);
    }

    public function deleteProject($id) {
        $this->getTable()->where('id', $id)->delete();
    }

    public function proposeProject($id, $rounds_id) {
        $this->getTable()->where('id', $id)->update([
            'state' => 'proposed'
        ]);

        $this->table('grabthelab_proposals')->insert([
            'grabthelab_project_id' => $id,
            'grabthelab_rounds_id' => $rounds_id,
            'proposed_at' => new \DateTime()
        ]);
    }
};
