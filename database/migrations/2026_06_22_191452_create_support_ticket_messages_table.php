<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('support_ticket_messages')) {
            Schema::create('support_ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('support_ticket_id');
                $table->unsignedBigInteger('sender_id');
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('support_ticket_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('support_ticket_messages', 'support_ticket_id')) {
                $table->unsignedBigInteger('support_ticket_id')->after('id');
            }

            if (!Schema::hasColumn('support_ticket_messages', 'sender_id')) {
                $table->unsignedBigInteger('sender_id')->after('support_ticket_id');
            }

            if (!Schema::hasColumn('support_ticket_messages', 'message')) {
                $table->text('message')->after('sender_id');
            }

            if (!Schema::hasColumn('support_ticket_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('message');
            }

            if (!Schema::hasColumn('support_ticket_messages', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
    }
};