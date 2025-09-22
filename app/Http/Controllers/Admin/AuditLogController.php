<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index()
    {
        // Stub simples para evitar erro de rota
        return response()->json(['stub'=>'admin.audit-logs.index']);
    }
}

