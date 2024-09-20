<?php

declare(strict_types=1);

namespace MrxKun\FastConnect;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\utils\TextFormat as TF;
use pocketmine\scheduler\ClosureTask;

class Main extends PluginBase implements Listener {
    private bool $enabled = true;
    private array $playerCache = [];
    private array $messages = [];
    private string $currentLanguage = "VI";

    public function onEnable(): void {
        $this->createLanguageFile();
        $this->loadLanguage();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
            function(): void {
                $this->adjustViewDistance();
            }
        ), 1200); // Chạy mỗi phút
        $this->getLogger()->info(TF::GREEN . $this->getMessage("plugin_enabled"));
    }

    private function createLanguageFile(): void {
        $languageFile = $this->getDataFolder() . "language.json";
        if (!file_exists($languageFile)) {
            $defaultLanguages = [
                "EN" => [
                    "plugin_enabled" => "FastConnect has been activated! ⚡",
                    "plugin_disabled" => "FastConnect has been deactivated! 🔌",
                    "command_usage" => "Usage: /fastconnect <enable|disable|status|cache|language>",
                    "fastconnect_enabled" => "FastConnect has been enabled! ✅",
                    "fastconnect_disabled" => "FastConnect has been disabled! ❌",
                    "status_enabled" => "enabled",
                    "status_disabled" => "disabled",
                    "status_message" => "FastConnect status: {status}",
                    "cache_players" => "Number of players in cache: {count}",
                    "cache_cleared" => "Cache has been cleared! 🧹",
                    "invalid_choice" => "Invalid choice. Usage: /fastconnect <enable|disable|status|cache clear|language>",
                    "view_distance_adjusted" => "View distance adjusted to {distance} 👀",
                    "language_changed" => "Language changed to English",
                    "current_language" => "Current language: English"
                ],
                "VI" => [
                    "plugin_enabled" => "FastConnect đã được kích hoạt! ⚡",
                    "plugin_disabled" => "FastConnect đã bị vô hiệu hóa! 🔌",
                    "command_usage" => "Sử dụng: /fastconnect <enable|disable|status|cache|language>",
                    "fastconnect_enabled" => "FastConnect đã được bật! ✅",
                    "fastconnect_disabled" => "FastConnect đã bị tắt! ❌",
                    "status_enabled" => "đang bật",
                    "status_disabled" => "đang tắt",
                    "status_message" => "Trạng thái FastConnect: {status}",
                    "cache_players" => "Số lượng người chơi trong cache: {count}",
                    "cache_cleared" => "Cache đã được xóa! 🧹",
                    "invalid_choice" => "Lựa chọn không hợp lệ. Sử dụng: /fastconnect <enable|disable|status|cache clear|language>",
                    "view_distance_adjusted" => "Khoảng cách nhìn đã được điều chỉnh thành {distance} 👀",
                    "language_changed" => "Đã chuyển ngôn ngữ sang tiếng Việt",
                    "current_language" => "Ngôn ngữ hiện tại: Tiếng Việt"
                ]
            ];
            file_put_contents($languageFile, json_encode($defaultLanguages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    private function loadLanguage(): void {
        $languageFile = $this->getDataFolder() . "language.json";
        $languageData = json_decode(file_get_contents($languageFile), true);
        $this->messages = $languageData[$this->currentLanguage] ?? $languageData["VI"];
    }

    private function getMessage(string $key, array $params = []): string {
        $message = $this->messages[$key] ?? "Message not found: $key";
        foreach ($params as $param => $value) {
            $message = str_replace("{{$param}}", (string)$value, $message);
        }
        return $message;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() !== "fastconnect") {
            return false;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(TF::YELLOW . $this->getMessage("command_usage"));
            return true;
        }

        switch ($args[0]) {
            case "enable":
                $this->enabled = true;
                $sender->sendMessage(TF::GREEN . $this->getMessage("fastconnect_enabled"));
                break;
            case "disable":
                $this->enabled = false;
                $sender->sendMessage(TF::RED . $this->getMessage("fastconnect_disabled"));
                break;
            case "status":
                $status = $this->enabled ? TF::GREEN . $this->getMessage("status_enabled") : TF::RED . $this->getMessage("status_disabled");
                $sender->sendMessage(TF::YELLOW . $this->getMessage("status_message", ["status" => $status]));
                $sender->sendMessage(TF::YELLOW . $this->getMessage("cache_players", ["count" => count($this->playerCache)]));
                break;
            case "cache":
                if (isset($args[1]) && $args[1] === "clear") {
                    $this->playerCache = [];
                    $sender->sendMessage(TF::GREEN . $this->getMessage("cache_cleared"));
                } else {
                    $sender->sendMessage(TF::YELLOW . $this->getMessage("command_usage"));
                }
                break;
            case "language":
                if (isset($args[1]) && in_array(strtoupper($args[1]), ["EN", "VI"])) {
                    $this->currentLanguage = strtoupper($args[1]);
                    $this->loadLanguage();
                    $sender->sendMessage(TF::GREEN . $this->getMessage("language_changed"));
                } else {
                    $sender->sendMessage(TF::YELLOW . $this->getMessage("current_language"));
                }
                break;
            default:
                $sender->sendMessage(TF::RED . $this->getMessage("invalid_choice"));
        }

        return true;
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void {
        if (!$this->enabled) {
            return;
        }

        $packet = $event->getPacket();
        if ($packet instanceof LoginPacket) {
            $this->getLogger()->debug("Nhận gói tin đăng nhập từ " . $event->getOrigin()->getIp());
        }
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $this->playerCache[$player->getName()] = [
            'lastJoin' => time(),
            'joinCount' => ($this->playerCache[$player->getName()]['joinCount'] ?? 0) + 1
        ];
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        if (isset($this->playerCache[$player->getName()])) {
            $this->playerCache[$player->getName()]['lastQuit'] = time();
        }
    }

    private function adjustViewDistance(): void {
        $playerCount = count($this->getServer()->getOnlinePlayers());
        $newViewDistance = max(4, min(10, 12 - intval($playerCount / 10)));
        
        foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
            $world->setChunkTickRadius($newViewDistance);
        }
        
        $this->getLogger()->info(TF::YELLOW . $this->getMessage("view_distance_adjusted", ["distance" => $newViewDistance]));
    }

    public function onDisable(): void {
        $this->getLogger()->info(TF::RED . $this->getMessage("plugin_disabled"));
    }
}
