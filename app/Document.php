<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('documents.show', $this);
    }
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'document_type_id');
    }
}
