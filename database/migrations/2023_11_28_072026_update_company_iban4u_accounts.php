<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_iban4u_accounts', function (Blueprint $table) {
            $table->string('annual_turnover')->change();
        });
    }

    public function down()
    {
        Schema::table('company_iban4u_accounts', function (Blueprint $table) {
            $table->integer('annual_turnover')->change();
        });
    }
};
