<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDataBackup;
use Illuminate\Http\Request;

class UserDataBackupController extends Controller
{
    /**
     * Exibe uma lista dos backups de dados de usuários.
     */
    public function index()
    {
        $backups = UserDataBackup::latest()->paginate(20);
        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Força o download do backup em PDF.
     */
    public function download(UserDataBackup $backup)
    {
        // Define o nome do arquivo para o download
        $fileName = 'backup_' . str_replace(' ', '_', $backup->deleted_user_name) . '_' . $backup->created_at->format('Ymd_His') . '.pdf';

        // Retorna o conteúdo do PDF armazenado no banco de dados como uma resposta de download
        return response($backup->pdf_content)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
