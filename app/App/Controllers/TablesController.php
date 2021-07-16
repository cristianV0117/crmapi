<?php namespace App\Controllers;
use Exceptions\QueryException;
use Core\Base;
use Carbon\Carbon;
final class TablesController extends Base
{
    public function tables()
    {
        try {
            // CONTACTS  //
            $this->DB()::schema('crm')->create('Contacts', function ($table) {
                $table->increments('Id');
                $table->string('Name', 55);
                $table->string('Cellphone', 15);
                $table->string('Email', 125)->unique();
                $table->text('Petition');
                $table->string('User', 125);
                $table->tinyInteger('Status');
                $table->timestamps();
            });
            // CHANNELS //
            $this->DB()::schema('crm')->create('Channels', function ($table) {
                $table->increments('Id');
                $table->string('Name', 45);
                $table->tinyInteger('Status');
                $table->timestamps();
            });
            // CONTACTS CHANNELS //
            $this->DB()::schema('crm')->create('ContactsChannels', function ($table) {
                $table->increments('Id');
                $table->integer('ContactsId')->unsigned();
                $table->integer('ChannelsId')->unsigned();
                $table->foreign('ContactsId') 
                    ->references('Id')
                    ->on('Contacts')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->foreign('ChannelsId')
                    ->references('Id')
                    ->on('Channels')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
            // TYPES CHANNELS //
            $this->DB()::schema('crm')->create('TypesChannels', function ($table) {
                $table->increments('Id');
                $table->string('Name', 45);
                $table->integer('ChannelsId');
                $table->tinyInteger('Status');
                $table->timestamps();
                $table->foreign('ChannelsId')
                    ->references('Id')
                    ->on('Channels')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
            // CONTACTS TYPES CHANNELS //
            $this->DB()::schema('crm')->create('ContactsTypesChannels', function ($table) {
                $table->increments('Id');
                $table->integer('ContactsId')->unsigned();
                $table->integer('TypesChannelsId')->unsigned();
                $table->foreign('ContactsId')
                    ->references('Id')
                    ->on('Contacts')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->foreign('TypesChannelsId')
                    ->references('Id')
                    ->on('TypesChannels')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
            // TYPES OBSERVATIONS //
            $this->DB()::schema('crm')->create('TypesObservations', function ($table) {
                $table->increments('Id');
                $table->string('Name', 45);
                $table->tinyInteger('Status');
                $table->timestamps();
            });
            // TRACINGS //
            $this->DB()::schema('crm')->create('Tracings', function ($table) {
                $table->increments('Id');
                $table->longText('Observation');
                $table->integer('TypesObservationsId');
                $table->integer('ContactsId');
                $table->integer('TypesChannelsId');
                $table->integer('UsersId');
                $table->tinyInteger('Status');
                $table->tinyInteger('Quotation')->nullable()->default(0);
                $table->float('Price')->nullable()->default(0);
                $table->timestamp('QuotationDate')->nullable();
                $table->tinyInteger('Sale')->nullable()->default(0);
                $table->float('Value')->nullable()->default(0);
                $table->timestamp('SaleDate')->nullable();
                $table->tinyInteger('Auto')->default(0);
                $table->timestamps();
                $table->foreign('TypesObservationsId')
                    ->references('Id')
                    ->on('TypesObservations')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->foreign('ContactsId')
                    ->references('Id')
                    ->on('Contacts')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->foreign('TypesChannelsId')
                    ->references('Id')
                    ->on('TypesChannels')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
            // TYPES TASKS //
            $this->DB()::schema('crm')->create('TypesTasks', function ($table) {
                $table->increments('Id');
                $table->string('Name');
                $table->tinyInteger('Status');
                $table->timestamps();
            });
            // TASKS //
            $this->DB()::schema('crm')->create('Tasks', function ($table) {
                $table->increments('Id');
                $table->longText('Description');
                $table->integer('Status');
                $table->integer('TracingsId');
                $table->integer('TypesTasksId');
                $table->timestamp('DeadLine');
                $table->string('User', 125);
                $table->timestamps();
                $table->foreign('TracingsId')
                    ->references('Id')
                    ->on('Tracings')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->foreign('TypesTasksId')
                    ->references('Id')
                    ->on('TypesTasks')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            throw new QueryException($e->getMessage(), 500);
        }
    }

    public function defaults()
    {
        $this->DB()::connection('crm')->table('TypesObservations')->insert([
            'Name' => "Automatico",
            'Status' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        $this->DB()::connection('crm')->table('Channels')->insert([
            'Name' => "Automatico",
            'Status' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        $this->DB()::connection('crm')->table('TypesChannels')->insert([
            'Name' => "Automatico",
            'ChannelsId' => 1,
            'Status' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

    public function down()
    {
        try {
            $this->DB()::schema('crm')->dropIfExists('ContactsChannels');
            $this->DB()::schema('crm')->dropIfExists('ContactsTypesChannels');
            $this->DB()::schema('crm')->dropIfExists('Tasks');
            $this->DB()::schema('crm')->dropIfExists('Tracings');
            $this->DB()::schema('crm')->dropIfExists('Contacts');
            $this->DB()::schema('crm')->dropIfExists('TypesChannels');
            $this->DB()::schema('crm')->dropIfExists('Channels');
            $this->DB()::schema('crm')->dropIfExists('TypesObservations');
            $this->DB()::schema('crm')->dropIfExists('TypesTasks');
        } catch (\Illuminate\Database\QueryException $e) {
            throw new QueryException($e->getMessage(), 500);
        }
    }
}