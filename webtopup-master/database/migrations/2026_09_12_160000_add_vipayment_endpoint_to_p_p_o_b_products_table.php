<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p_p_o_b_products', function (Blueprint $table) {
            $table->string('vipayment_endpoint')->nullable()->after('provider');
        });
    }

    public function down(): void
    {
        Schema::table('p_p_o_b_products', function (Blueprint $table) {
            $table->dropColumn('vipayment_endpoint');
        });
    }
};
