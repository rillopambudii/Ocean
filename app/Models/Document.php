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

        'technical' => ['label' => 'Technical', 'gate' => 'super-admin'],

        'crewing' => ['label' => 'Crewing', 'gate' => 'super-admin'],

        'purchasing' => ['label' => 'Purchasing', 'gate' => 'super-admin'],

        'budget' => ['label' => 'Budget Plan & Monitoring', 'gate' => 'super-admin'],
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
