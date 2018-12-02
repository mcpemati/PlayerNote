<?php

namespace PlayerNote;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\event\server\DataPacketReceiveEvent;

class Main extends PluginBase implements Listener {

	public function onEnable() {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("§a[起動] §bPlayerNote §aを起動しました。");
	}

	public function onDisable() {
		$this->getLogger()->info("§c[終了] §bPlayerNote §aを終了しています...");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if (!$sender instanceof Player) {
			$sender->sendMessage(TextFormat::RED . "プレイヤーのみ利用可能です。");
			return true;
		}

		if ($label === "pn") {
			$data = [
				"type" => "custom_form",
				"title" => "§lＰｌａｙｅｒＮｏｔｅ",
				"content" => [
					[
						"type" => "label",
						"text" => "§lプレイヤーの名前を記入してください。"
					],
					[
						"type" => "input",
						"text" => "§l名前",
						"placeholder" => "",
						"default" => ""
					]
				]
			];
			$this->createWindow($sender, $data, 571895);
		}return true;
	}

	public function onDataPacketReceiveEvent(DataPacketReceiveEvent $event) {
		$pk = $event->getPacket();
		$player = $event->getPlayer();
		if($pk instanceof ModalFormResponsePacket) {
			$id = $pk->formId;
			$data = $pk->formData;
			$result = json_decode($data);
			if($data == "null\n") {
			} else {
				if ($id === 571895) {
					$pplayer = $this->getServer()->getPlayer($result[1]);
					if ($result[1] === "") {
						$player->sendMessage("§l§c⚠ 記入されていません。");
						return true;
					} elseif (!isset($pplayer)) {
						$player->sendMessage("§l§c⚠ そのプレイヤーは現在このサーバー内に存在しません。");
						return true;
					} else {
						$ping = $pplayer->getPing();
						$color = ($ping < 150 ? TextFormat::GREEN : ($ping < 250 ? TextFormat::GOLD : TextFormat::RED));
						$data = [
							"type" => "custom_form",
							"title" => "§l ".$pplayer->getName()." / ＰｌａｙｅｒＮｏｔｅ",
							"content" => [
								[
									"type" => "label",
									"text" => "§l言語: {$pplayer->getLocale()}"
								],
								[
									"type" => "label",
									"text" => "§l応答速度: {$color}{$ping}ms"
								],
								[
									"type" => "label",
									"text" => "§lIPアドレス: {$pplayer->getAddress()}"
								],
								[
									"type" => "label",
									"text" => "§lホスト: ".gethostbyaddr($pplayer->getAddress())
								],
								[
									"type" => "label",
									"text" => "§lポート: {$pplayer->getPort()}"
								],
								[
									"type" => "label",
									"text" => "§lクライアントID: {$pplayer->getClientId()}"
								],
								[
									"type" => "label",
									"text" => "§lＸＵＩＤ: {$pplayer->getXuid()}"
								],
								[
									"type" => "label",
									"text" => "§lＵＵＩＤ: {$pplayer->getUniqueId()}"
								],
								[
									"type" => "label",
									"text" => "§lワールド: {$pplayer->getLevel()->getFolderName()}"
								],
								[
									"type" => "label",
									"text" => "§l座標: x:{$pplayer->getX()} y:{$pplayer->getY()} z:{$pplayer->getZ()}"
								],
								[
									"type" => "input",
									"text" => "§lＮａｍｅＴａｇ",
									"placeholder" => "",
									"default" => $pplayer->getNameTag()
								],
								[
									"type" => "input",
									"text" => "§lＤｉｓｐｌａｙＮａｍｅ",
									"placeholder" => "",
									"default" => $pplayer->getDisplayName()
								],
								[
									"type" => "step_slider",
									"text" => "§lゲームモード",
									"steps" => array("§l§aサバイバル", "§l§eクリエイティブ", "§l§cアドベンチャー", "§l§bスペクテイター"),
									"default" => $pplayer->getGamemode()
								],
								[
									"type" => "label",
									"text" => "§l体力: {$pplayer->getHealth()}"
								],
								[
									"type" => "label",
									"text" => "§l最大体力: {$pplayer->getMaxHealth()}"
								],
								[
									"type" => "label",
									"text" => "§l空腹度: {$pplayer->getFood()}"
								],
								[
									"type" => "input",
									"text" => "§l§c⚠ ここを扱わないでください。",
									"placeholder" => "{$pplayer->getName()}",
									"default" => $pplayer->getName()
								]
							]
						];
						$this->createWindow($player, $data, 571896);
					}
				} elseif ($id === 571896) {
					$pplayer = $this->getServer()->getPlayer($result[17]);
					if (!isset($pplayer)) {
						$player->sendMessage("§l§c⚠ そのプレイヤーは現在このサーバー内に存在しません。");
						return true;
					} else {
						$pplayer->setNameTag($result[10]);
						$pplayer->setDisplayName($result[11]);
						$pplayer->setGamemode($result[12]);
						$player->sendMessage("§l§e送信しました。");
					}
				}
			}
		}
	}

	public function createWindow(Player $player, $data, int $id){
		$pk = new ModalFormRequestPacket();
		$pk->formId = $id;
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$player->dataPacket($pk);
	}
}