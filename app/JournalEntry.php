<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    public function path()
    {
        return route('journal_entries.show', $this);
    }
}
