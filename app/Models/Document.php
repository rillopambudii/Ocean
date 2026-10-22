<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /**
     * Kategori dokumen: slug URL => label menu & gate yang boleh mengakses.
     */
    public const CATEGORIES = [

        'manual' => ['label' => 'Manual', 'gate' => 'qhse-access'],

        'procedure' => ['label' => 'Procedure', 'gate' => 'qhse-access'],

        'work-instruction' => ['label' => 'Work Instruction', 'gate' => 'qhse-access'],

        'form-checklist' => ['label' => 'Form/Checklist', 'gate' => 'qhse-access'],

        'technical' => ['label' => 'Technical', 'gate' => 'technical-access'],

        'crewing' => ['label' => 'Crewing', 'gate' => 'crewing-access'],

        'purchasing' => ['label' => 'Purchasing', 'gate' => 'purchasing-access'],

        'budget' => ['label' => 'Budget Plan & Monitoring', 'gate' => 'technical-access'],
    ];

    protected $fillable = [

        'category',

        'title',

        'document_number',

        'revision',

        'file_path',

        'original_name',

        'uploaded_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by'
        );
    }
}
