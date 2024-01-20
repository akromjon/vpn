<?php
namespace App\Models\Client\Enum;

enum ClientAction: string
{
    case TOKEN_GENERATED = "token_generated";
    case LIST_SERVERS = "list_servers";
    case DOWNLOADED_CONFIG = "downloaded_config";
    case CONNECTING = "connecting";
    case CONNECTED = "connected";
    case DISCONNECTING = "disconnecting";
    case DISCONNECTED = "disconnected";

    case DELETED_APP= "deleted_app";

}
