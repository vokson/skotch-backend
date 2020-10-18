<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdfMergeFile extends Model
{
    use HasFactory;

    protected $dateFormat = 'U';
    protected $table = 'pdf_merge_files';
}
