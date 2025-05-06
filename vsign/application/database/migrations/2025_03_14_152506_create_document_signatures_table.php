<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_signatures', static function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('request_id')
                ->references('id')
                ->on('document_signature_requests')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('signed_pdf_path');
            $table->timestamp('signed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
    }
};
