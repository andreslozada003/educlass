<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class WhatsAppTestCommand extends Command
{
    protected $signature = 'whatsapp:test {to : Numero destino, ej 573001234567} {message? : Mensaje a enviar}';

    protected $description = 'Envia un mensaje de prueba por WhatsApp Cloud API.';

    public function handle(WhatsAppService $whatsAppService): int
    {
        if (!$whatsAppService->enabled()) {
            $this->error('WhatsApp no esta habilitado. Revisa WHATSAPP_ENABLED, WHATSAPP_PHONE_NUMBER_ID y WHATSAPP_TOKEN en .env');
            return self::FAILURE;
        }

        $to = (string) $this->argument('to');
        $message = (string) ($this->argument('message') ?: 'Mensaje de prueba desde Educlass.');

        $ok = $whatsAppService->sendText($to, $message);
        if (!$ok) {
            $this->error('No se pudo enviar el mensaje. Revisa token, phone_number_id y numero destino.');
            return self::FAILURE;
        }

        $this->info('Mensaje enviado correctamente por WhatsApp.');
        return self::SUCCESS;
    }
}

