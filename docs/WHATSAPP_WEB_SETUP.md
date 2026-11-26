# WhatsApp no APS Dream Home — o que existe, o que funciona e o que falta

## Resposta curta

Sim, o projeto já tem uma ponte **WhatsApp Web** (`whatsapp-service/`, baseada em `whatsapp-web.js` + Puppeteer) e uma página admin para conectá-la por QR (`/admin/whatsapp-web`). Depois de conectada, o PHP consegue enviar mensagens e OTP pelo número conectado. **Mas dois pré-requisitos não estão prontos nesta máquina**: a ponte não inicia o navegador (incompatibilidade Node 24 / Puppeteer) e ninguém escanou o QR ainda. Enquanto isso, `sendWhatsAppOTP()` falha fechado — não finge que enviou.

Nada é enviado para clientes sem que o dono conecte o QR e autorize o envio. Isto é intencional.

## O que já existe no código

| Peça | Arquivo | Estado |
|---|---|---|
| Ponte WhatsApp Web (QR, status, send, media, logout, auto-reconnect) | `whatsapp-service/server.js` | Implementada; porta padrão 3001 |
| Página admin de conexão (QR + status + reconectar + logout) | `app/views/admin/whatsapp-web/index.php` | Existe |
| Cliente PHP da ponte (`/api/status`, `/api/send`, `/api/qr`, ...) | `app/Services/Communication/WhatsAppWebService.php` | Implementado |
| Envio de OTP por WhatsApp | `app/Services/OTPService.php::sendWhatsAppOTP()` | **Corrigido em S795**: usa a ponte conectada e falha fechado; nunca mais grava o OTP no log |
| Health check mostrando o status real do WhatsApp | `AICallingController@healthCheck()` | **Corrigido em S795**: antes tratava o array de resposta como booleano e dizia "conectado" mesmo com a ponte caída |
| WhatsApp Business API oficial (Graph API) | `WhatsAppService.php`, `WhatsAppSenderService.php` | Código existe e credenciais estão no `.env`; validade das credenciais não foi provada nesta máquina |
| Fila / templates / broadcast / fluxos | `whatsapp_messages`, `marketing_*`, `WhatsAppManager`, `WhatsAppFlowService` | Implementados; envio real depende de provider conectado |

## Limites importantes (honestos)

- **WhatsApp Web (pessoal)** não é API oficial. Não garante entrega, não tem templates aprovados e pode ser bloqueado pelo Meta se usado para disparos em massa. Serve para fluxos baixos e dirigidos (resposta a quem escreveu, OTP de um usuário que pediu, lembretes individuais).
- **WhatsApp Business Cloud API** é o caminho oficial: exige token válido, número aprovado e templates para mensagens fora da janela de 24 h. É o caminho correto para campanhas em escala.
- **OTP por WhatsApp** é útil, mas a ponte Web depende da sessão do navegador. Se a sessão cair, o OTP falha fechado — por isso SMS/e-mail continuam sendo os canais primários. Nenhum código de OTP é escrito em log.
- **Agente em background** com limites próprios é viável (fila + cron + rate limit por minuto), mas envio externo real continua sendo decisão do dono. O `checkRateLimit()` do `CampaignService` já mostra o padrão (100 e-mail / 50 SMS / 30 WhatsApp por minuto).

## Como habilitar (passos do dono)

1. **Resolver o runtime da ponte** — nesta máquina, `node v24.14.1` + `whatsapp-web.js 1.26` / Puppeteer 24 falham com
   `Cannot read properties of null (reading 'Socket')`. Use Node 20 LTS ou 22, ou atualize/substitua a ponte (ex.: Baileys) antes de prosseguir. O Chrome 146 exigido pelo Puppeteer já foi instalado no cache (`C:\Users\abhay\.cache\puppeteer\chrome\win64-146.0.7680.31`).
2. **Iniciar a ponte** (detached, na raiz do projeto):
   `Start-Process -FilePath node -ArgumentList 'server.js' -WorkingDirectory '<projeto>\whatsapp-service' -WindowStyle Hidden`
3. **Conferir**: `curl http://127.0.0.1:3001/api/status` → `{"connected":false,...}` e `curl http://127.0.0.1:3001/api/qr` → QR em data URL.
4. **Escanear o QR** no painel `/admin/whatsapp-web` com o WhatsApp do número que será usado. Depois disso `/api/status` deve mostrar `connected:true` e o número.
5. **Só então** OTP/campanhas por WhatsApp passam a enviar de fato. A ponte persiste a sessão em `whatsapp-service/session/` e se reconecta sozinha.

Se preferir não usar WhatsApp Web, a alternativa oficial é validar as credenciais da Cloud API em `/admin/service-configs` e usar `WhatsAppService::sendTextMessage()` / `sendTemplateMessage()`.

## Estado após S795

- `sendWhatsAppOTP()` **não** loga mais o código e **não** retorna sucesso falso: sem ponte conectada, retorna `false` e o fluxo reporta falha ao usuário.
- Health check do WhatsApp no `/admin/ai-calling/health` agora informa `status=error, message=Connection refused` quando a ponte está caída (antes dizia "connected").
- Ponte **parada** e sem QR escaneado nesta máquina — nenhuma mensagem foi enviada durante a auditoria.
