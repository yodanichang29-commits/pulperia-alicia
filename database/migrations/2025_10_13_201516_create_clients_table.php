<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(){
    Schema::create('clients', function(Blueprint $t){
      $t->id();
      $t->string('name');
      $t->string('phone')->nullable();
      $t->timestamps();
    });
  }
  public function down(){ Schema::dropIfExists('clients'); }
};
