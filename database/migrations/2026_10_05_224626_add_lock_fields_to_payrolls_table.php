<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {

            $table->unsignedBigInteger('approved_by')
                ->nullable()
                ->after('status');

            $table->timestamp('approved_at')
                ->nullable()
                ->after('approved_by');

            $table->timestamp('locked_at')
                ->nullable()
                ->after('approved_at');

            $table->timestamp('paid_at')
                ->nullable()
                ->after('locked_at');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {

            $table->dropColumn([
                'approved_by',
                'approved_at',
                'locked_at',
                'paid_at'
            ]);

        });
    }
};