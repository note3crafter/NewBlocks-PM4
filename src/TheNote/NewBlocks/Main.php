<?php

namespace TheNote\NewBlocks;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockIdentifierFlattened as BIDFlattened;
use pocketmine\block\BlockIdentifierFlattened as BIF;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\BlockToolType;
use pocketmine\block\Door;
use pocketmine\block\Fence;
use pocketmine\block\FenceGate;
use pocketmine\block\Opaque;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\StonePressurePlate;
use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\utils\RecordType;
use pocketmine\block\utils\SlabType;
use pocketmine\block\utils\TreeType;
use pocketmine\block\Wall;
use pocketmine\block\WoodenButton;
use pocketmine\block\WoodenPressurePlate;
use pocketmine\block\WoodenTrapdoor;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\FurnaceRecipeManager;
use pocketmine\crafting\FurnaceType;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\LegacyBlockIdToStringIdMap;
use pocketmine\data\bedrock\LegacyToStringBidirectionalIdMap;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemBlockWallOrFloor;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\item\Pickaxe;
use pocketmine\item\Record;
use pocketmine\item\Shovel;
use pocketmine\item\StringToItemParser;
use pocketmine\item\Sword;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\lang\Translatable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\ItemTranslator;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\FilterTextPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\inventory\ReleaseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\CraftRecipeOptionalStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\sound\AnvilUseSound;
use pocketmine\world\sound\Sound;
use pocketmine\world\World;
use ReflectionClass;
use TheNote\NewBlocks\blocks\GoldOre;
use TheNote\NewBlocks\blocks\IronOre;
use TheNote\NewBlocks\blocks\Lectern;
use TheNote\NewBlocks\blocks\Basalt;
use TheNote\NewBlocks\blocks\Beehive;
use TheNote\NewBlocks\blocks\BeeNest;
use TheNote\NewBlocks\blocks\Blackstone;
use TheNote\NewBlocks\blocks\Campfire;
use TheNote\NewBlocks\blocks\Chain;
use TheNote\NewBlocks\blocks\ChiseledPolishedBlackstone;
use TheNote\NewBlocks\blocks\Composter;
use TheNote\NewBlocks\blocks\CryingObsidian;
use TheNote\NewBlocks\blocks\Dispenser;
use TheNote\NewBlocks\blocks\Dropper;
use TheNote\NewBlocks\blocks\FloorSign;
use TheNote\NewBlocks\blocks\FlowerPot;
use TheNote\NewBlocks\blocks\Fungus;
use TheNote\NewBlocks\blocks\GildedBlackstone;
use TheNote\NewBlocks\blocks\HoneyBlock;
use TheNote\NewBlocks\blocks\HoneyCombBlock;
use TheNote\NewBlocks\blocks\Hyphae;
use TheNote\NewBlocks\blocks\Log;
use TheNote\NewBlocks\blocks\NetherGoldOre;
use TheNote\NewBlocks\blocks\Nylium;
use TheNote\NewBlocks\blocks\Planks;
use TheNote\NewBlocks\blocks\PolishedBasalt;
use TheNote\NewBlocks\blocks\PolishedBlackStone;
use TheNote\NewBlocks\blocks\PolishedBlackstoneButton;
use TheNote\NewBlocks\blocks\RespawnAnchor;
use TheNote\NewBlocks\blocks\Roots;
use TheNote\NewBlocks\blocks\Sculk;
use TheNote\NewBlocks\blocks\Shroomlight;
use TheNote\NewBlocks\blocks\SoulCampfire;
use TheNote\NewBlocks\blocks\SoulFire;
use TheNote\NewBlocks\blocks\SoulLantern;
use TheNote\NewBlocks\blocks\SoulSoil;
use TheNote\NewBlocks\blocks\SoulTorch;
use TheNote\NewBlocks\blocks\Stonecutter;
use TheNote\NewBlocks\blocks\TwistingVines;
use TheNote\NewBlocks\blocks\WallSign;
use TheNote\NewBlocks\blocks\WarpedFungus;
use TheNote\NewBlocks\blocks\WarpedNylium;
use TheNote\NewBlocks\blocks\WarpedWartBlock;
use TheNote\NewBlocks\blocks\WeepingVines;
use TheNote\NewBlocks\commands\ToggleDownFallCommand;
use TheNote\NewBlocks\commands\WeatherCommand;
use TheNote\NewBlocks\entity\EntityManager;
use TheNote\NewBlocks\entity\object\FireworkRocketEntity;
use TheNote\NewBlocks\items\AmethystShard;
use TheNote\NewBlocks\items\Crossbow;
use TheNote\NewBlocks\items\ElytraItem;
use TheNote\NewBlocks\items\FireChargeItem;
use TheNote\NewBlocks\items\FireworkRocketItem;
use TheNote\NewBlocks\items\FlintAndSteel;
use TheNote\NewBlocks\items\GoatHorn;
use TheNote\NewBlocks\items\LodestoneCompass;
use TheNote\NewBlocks\items\Shield;
use TheNote\NewBlocks\items\Spyglass;
use TheNote\NewBlocks\items\Trident;
use TheNote\NewBlocks\tiles\Campfire as TileCampfire;
use TheNote\NewBlocks\blocks\AmethystBlock;
use TheNote\NewBlocks\blocks\AmethystCluster;
use TheNote\NewBlocks\blocks\Azalea;
use TheNote\NewBlocks\blocks\AzaleaLeaves;
use TheNote\NewBlocks\blocks\AzaleaLeavesFlowered;
use TheNote\NewBlocks\blocks\BigDripleaf;
use TheNote\NewBlocks\blocks\BuddingAmethyst;
use TheNote\NewBlocks\blocks\Calcite;
use TheNote\NewBlocks\blocks\Candle;
use TheNote\NewBlocks\blocks\CaveVines;
use TheNote\NewBlocks\blocks\ChiseledDeepslate;
use TheNote\NewBlocks\blocks\CobbledDeepslate;
use TheNote\NewBlocks\blocks\CobbledDeepslateSlab;
use TheNote\NewBlocks\blocks\CobbledDeepslateStairs;
use TheNote\NewBlocks\blocks\CobbledDeepslateWall;
use TheNote\NewBlocks\blocks\CopperBlock;
use TheNote\NewBlocks\blocks\CopperOre;
use TheNote\NewBlocks\blocks\CrackedDeepslateBricks;
use TheNote\NewBlocks\blocks\CrackedDeepslateTiles;
use TheNote\NewBlocks\blocks\CutCopper;
use TheNote\NewBlocks\blocks\CutCopperSlab;
use TheNote\NewBlocks\blocks\CutCopperStairs;
use TheNote\NewBlocks\blocks\Deepslate;
use TheNote\NewBlocks\blocks\DeepslateBricks;
use TheNote\NewBlocks\blocks\DeepslateBrickSlab;
use TheNote\NewBlocks\blocks\DeepslateBrickStairs;
use TheNote\NewBlocks\blocks\DeepslateBrickWall;
use TheNote\NewBlocks\blocks\DeepslateCoalOre;
use TheNote\NewBlocks\blocks\DeepslateCopperOre;
use TheNote\NewBlocks\blocks\DeepslateDiamondOre;
use TheNote\NewBlocks\blocks\DeepslateEmeraldOre;
use TheNote\NewBlocks\blocks\DeepslateGoldOre;
use TheNote\NewBlocks\blocks\DeepslateIronOre;
use TheNote\NewBlocks\blocks\DeepslateLapisOre;
use TheNote\NewBlocks\blocks\DeepslateRedstoneOre;
use TheNote\NewBlocks\blocks\DeepslateTiles;
use TheNote\NewBlocks\blocks\DeepslateTileSlab;
use TheNote\NewBlocks\blocks\DeepslateTileStairs;
use TheNote\NewBlocks\blocks\DeepslateTileWall;
use TheNote\NewBlocks\blocks\DirtWithRoots;
use TheNote\NewBlocks\blocks\DoubleCutCopperSlab;
use TheNote\NewBlocks\blocks\DripstoneBlock;
use TheNote\NewBlocks\blocks\ExposedCopper;
use TheNote\NewBlocks\blocks\ExposedCutCopper;
use TheNote\NewBlocks\blocks\ExposedCutCopperSlab;
use TheNote\NewBlocks\blocks\ExposedCutCopperStairs;
use TheNote\NewBlocks\blocks\ExposedDoubleCutCopperSlab;
use TheNote\NewBlocks\blocks\FloweringAzalea;
use TheNote\NewBlocks\blocks\GlowFrame;
use TheNote\NewBlocks\blocks\HangingRoots;
use TheNote\NewBlocks\blocks\InfestedDeepslate;
use TheNote\NewBlocks\blocks\LargeAmethystBud;
use TheNote\NewBlocks\blocks\LightBlueCandle;
use TheNote\NewBlocks\blocks\LightningRod;
use TheNote\NewBlocks\blocks\LimeCandle;
use TheNote\NewBlocks\blocks\MagentaCandle;
use TheNote\NewBlocks\blocks\MediumAmethystBud;
use TheNote\NewBlocks\blocks\MossBlock;
use TheNote\NewBlocks\blocks\MossCarpet;
use TheNote\NewBlocks\blocks\OrangeCandle;
use TheNote\NewBlocks\blocks\OxidizedCopper;
use TheNote\NewBlocks\blocks\OxidizedCutCopper;
use TheNote\NewBlocks\blocks\OxidizedCutCopperSlab;
use TheNote\NewBlocks\blocks\OxidizedCutCopperStairs;
use TheNote\NewBlocks\blocks\OxidizedDoubleCutCopperSlab;
use TheNote\NewBlocks\blocks\PointedDripstone;
use TheNote\NewBlocks\blocks\PolishedDeepslate;
use TheNote\NewBlocks\blocks\PolishedDeepslateSlab;
use TheNote\NewBlocks\blocks\PolishedDeepslateStairs;
use TheNote\NewBlocks\blocks\PolishedDeepslateWall;
use TheNote\NewBlocks\blocks\PowderSnow;
use TheNote\NewBlocks\blocks\RawCopperBlock;
use TheNote\NewBlocks\blocks\RawGoldBlock;
use TheNote\NewBlocks\blocks\RawIronBlock;
use TheNote\NewBlocks\blocks\SculkSensor;
use TheNote\NewBlocks\blocks\SmallAmethystBud;
use TheNote\NewBlocks\blocks\SmallDripleafBlock;
use TheNote\NewBlocks\blocks\SmoothBasalt;
use TheNote\NewBlocks\blocks\SporeBlossom;
use TheNote\NewBlocks\blocks\TintedGlass;
use TheNote\NewBlocks\blocks\Tuff;
use TheNote\NewBlocks\blocks\WaxedCopper;
use TheNote\NewBlocks\blocks\WaxedCutCopper;
use TheNote\NewBlocks\blocks\WaxedCutCopperSlab;
use TheNote\NewBlocks\blocks\WaxedCutCopperStairs;
use TheNote\NewBlocks\blocks\WaxedDoubleCutCopperSlab;
use TheNote\NewBlocks\blocks\WaxedExposedCopper;
use TheNote\NewBlocks\blocks\WaxedExposedCutCopper;
use TheNote\NewBlocks\blocks\WaxedExposedCutCopperSlab;
use TheNote\NewBlocks\blocks\WaxedExposedCutCopperStairs;
use TheNote\NewBlocks\blocks\WaxedExposedDoubleCutCopperSlab;
use TheNote\NewBlocks\blocks\WaxedOxidizedCopper;
use TheNote\NewBlocks\blocks\WaxedOxidizedCutCopper;
use TheNote\NewBlocks\blocks\WaxedOxidizedCutCopperStairs;
use TheNote\NewBlocks\blocks\WaxedOxidizedCutCopperSlab;
use TheNote\NewBlocks\blocks\WaxedWeatheredCopper;
use TheNote\NewBlocks\blocks\WaxedWeatheredCutCopper;
use TheNote\NewBlocks\blocks\WaxedWeatheredCutCopperSlab;
use TheNote\NewBlocks\blocks\WaxedWeatheredCutCopperStairs;
use TheNote\NewBlocks\blocks\WaxedWeatheredDoubleCutCopperSlab;
use TheNote\NewBlocks\blocks\WeatheredCopper;
use TheNote\NewBlocks\blocks\WeatheredCutCopper;
use TheNote\NewBlocks\blocks\WeatheredCutCopperSlab;
use TheNote\NewBlocks\blocks\WeatheredCutCopperStairs;
use TheNote\NewBlocks\blocks\WeatheredDoubleCutCopperSlab;
use TheNote\NewBlocks\blocks\WhiteCandle;
use TheNote\NewBlocks\blocks\YellowCandle;
use TheNote\NewBlocks\tiles\Tiles;
use TheNote\NewBlocks\world\WorldManager;
use const pocketmine\BEDROCK_DATA_PATH;

class Main extends PluginBase implements Listener
{
    private static Main $instance;
    private WorldManager $worldManager;
    public static array $crossbowLoadData = [];

    public function onLoad(): void
    {
        $this->saveResource("settings.yml");
        self::$instance = $this;
        $this->blockinit();
        self::registerRuntimeIds();
        Tiles::init();
        $this->itemblockregister();
        $this->initItems();
        $this->furnace();
        EntityManager::init();
        $this->worldManager = new WorldManager();


        $asyncPool = $this->getServer()->getAsyncPool();
        $asyncPool->addWorkerStartHook(static function (int $worker) use ($asyncPool): void {
            $asyncPool->submitTaskToWorker(new class extends AsyncTask {
                public function onRun(): void
                {
                    Main::registerRuntimeIds();
                }
            }, $worker);
        });
        $this->crafting();

    }
    public static function getInstance(): Main{
        return self::$instance;
    }

    public function onEnable() : void{

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $enchMap = EnchantmentIdMap::getInstance();
        //$enchMap->register(EnchantmentIds::MULTISHOT, $multishot = new Enchantment("Multishot", Rarity::MYTHIC, ItemFlags::BOW, ItemFlags::NONE, 1));
        //$enchMap->register(EnchantmentIds::QUICK_CHARGE, $quickCharge = new Enchantment("Quick charge", Rarity::MYTHIC, ItemFlags::BOW, ItemFlags::NONE, 3));

        //StringToEnchantmentParser::getInstance()->register("multishot", fn() => $multishot);
        //StringToEnchantmentParser::getInstance()->register("quick_charge", fn() => $quickCharge);
        $this->worldManager->startup();
        $this->getServer()->getCommandMap()->register("weather", new WeatherCommand($this));
        $this->getServer()->getCommandMap()->register("toggledownfall", new ToggleDownFallCommand($this));


        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() use ($enchMap) : void{
            foreach(self::$crossbowLoadData as $name => $bool){
                $player = $this->getServer()->getPlayerExact($name);
                if($player !== null){
                    $itemInHand = $player->getInventory()->getItemInHand();
                    if($itemInHand instanceof Crossbow){
                        $quickCharge = $itemInHand->getEnchantmentLevel($enchMap->fromId(EnchantmentIds::QUICK_CHARGE));
                        $time = $player->getItemUseDuration();
                        if($time >= 24 - $quickCharge * 5){
                            $itemInHand->onReleaseUsing($player);
                            $player->getInventory()->setItemInHand($itemInHand);
                            unset(self::$crossbowLoadData[$name]);
                        }
                    }else{
                        unset(self::$crossbowLoadData[$name]);
                    }
                }else{
                    unset(self::$crossbowLoadData[$name]);
                }
            }
        }), 1);
    }

    //RuntimeBlockMapping::getInstance()->getBedrockKnownStates(ProtocolInfo::PROTOCOL_1_19_0) and RuntimeBlockMapping::getInstance()->getBedrockKnownStates(ProtocolInfo::PROTOCOL_1_19_20);
    public function getWorldManager(): WorldManager{
        return $this->worldManager;
    }
    /**
     * @throws \ReflectionException
     */
    public function blockinit()
    {
        self::registerBlockGlobally(new PowderSnow(new BID(561, 0, -306), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new SculkSensor(new BID(562, 0, -307), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new PointedDripstone(new BID(563, 0, -308), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new CopperOre(new BID(566, 0, -311)));
        self::registerBlockGlobally(new LightningRod(new BID(567, 0, -312), new BlockBreakInfo(3, BlockToolType::PICKAXE)));
        self::registerBlockGlobally(new DripstoneBlock(new BID(572, 0, -317), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new DirtWithRoots(new BID(573, 0, -318), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new HangingRoots(new BID(574, 0, -319), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new MossBlock(new BID(575, 0, -320), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new SporeBlossom(new BID(576, 0, -321), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new CaveVines(new BID(577, 0, -322), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new BigDripleaf(new BID(578, 0, -323), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new AzaleaLeaves(new BID(579, 0, -324), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new AzaleaLeavesFlowered(new BID(580, 0, -325), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new Calcite(new BID(581, 0, -326), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new AmethystBlock(new BID(582, 0, -327), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new BuddingAmethyst(new BID(583, 0, -328), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new AmethystCluster(new BID(584, 0, -329)));
        self::registerBlockGlobally(new LargeAmethystBud(new BID(585, 0, -330), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new MediumAmethystBud(new BID(586, 0, -331), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new SmallAmethystBud(new BID(587, 0, -332), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new Tuff(new BID(588, 0, -333), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new TintedGlass(new BID(589, 0, -334), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new MossCarpet(new BID(590, 0, -335), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new SmallDripleafBlock(new BID(591, 0, -336), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new Azalea(new BID(592, 0, -337), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new FloweringAzalea(new BID(593, 0, -338), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new GlowFrame(new BID(594, 0, -339), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new CopperBlock(new BID(595, 0, -340), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new ExposedCopper(new BID(596, 0, -341), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new WeatheredCopper(new BID(597, 0, -342), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new OxidizedCopper(new BID(598, 0, -343), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new WaxedCopper(new BID(599, 0, -344), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new WaxedExposedCopper(new BID(600, 0, -345), new BlockBreakInfo(3, BlockToolType::PICKAXE, -1)));
        self::registerBlockGlobally(new WaxedWeatheredCopper(new BID(601, 0, -346), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new CutCopper(new BID(602, 0, -347), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new ExposedCutCopper(new BID(603, 0, -348), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WeatheredCutCopper(new BID(604, 0, -349), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new OxidizedCutCopper(new BID(605, 0, -350), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedCutCopper(new BID(606, 0, -351), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedExposedCutCopper(new BID(607, 0, -352), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedWeatheredCutCopper(new BID(608, 0, -353), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new CutCopperStairs(new BID(609, 0, -354), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new ExposedCutCopperStairs(new BID(610, 0, -355), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WeatheredCutCopperStairs(new BID(611, 0, -356), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new OxidizedCutCopperStairs(new BID(612, 0, -357), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedCutCopperStairs(new BID(613, 0, -358), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedExposedCutCopperStairs(new BID(614, 0, -359), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedWeatheredCutCopperStairs(new BID(615, 0, -360), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new CutCopperSlab(new BIF(616, [623], 0, -361,), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new ExposedCutCopperSlab(new BIF(617, [624], 0, -362), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WeatheredCutCopperSlab(new BIF(618, [625], 0, -363), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new OxidizedCutCopperSlab(new BIF(619, [626], 0, -364), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedCutCopperSlab(new BIF(620, [627], 0, -365), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedExposedCutCopperSlab(new BIF(621, [628], 0, -366), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedWeatheredCutCopperSlab(new BIF(622, [629], 0, -367), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new DoubleCutCopperSlab(new BID(623, 0 , -368), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new ExposedDoubleCutCopperSlab(new BID(624, 0 , -369), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new WeatheredDoubleCutCopperSlab(new BID(625, 0 , -370), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new OxidizedDoubleCutCopperSlab(new BID(626, 0 , -371), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new WaxedDoubleCutCopperSlab(new BID(627, 0 , -372), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new WaxedExposedDoubleCutCopperSlab(new BID(628, 0 , -373), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new WaxedWeatheredDoubleCutCopperSlab(new BID(629, 0 , -374), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new CaveVinesBodyWithBerries(new BID(630, 0 , -375), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new CaveVinesHeadWithBerries(new BID(631, 0 , -376), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new SmoothBasalt(new BID(632, 0, -377), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new Deepslate(new BID(633, 0, -378), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new CobbledDeepslate(new BID(634, 0, -379), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new CobbledDeepslateSlab(new BIF(635, [651], 0, -380), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new CobbledDeepslateStairs(new BID(636, 0, -381), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new CobbledDeepslateWall(new BID(637, 0, -382), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new PolishedDeepslate(new BID(638, 0, -383), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new PolishedDeepslateSlab(new BIF(639, [652], 0, -384), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new PolishedDeepslateStairs(new BID(640, 0, -385), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new PolishedDeepslateWall(new BID(641, 0, -386), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateTiles(new BID(642, 0, -387), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateTileSlab(new BIF(643, [653], 0, -388), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateTileStairs(new BID(644, 0, -389), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateTileWall(new BID(645, 0, -390), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateBricks(new BID(646, 0, -391), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateBrickSlab(new BIF(647, [654], 0, -392), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateBrickStairs(new BID(648, 0, -393), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateBrickWall(new BID(649, 0, -394), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new ChiseledDeepslate(new BID(650, 0, -395), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new CobbledDeepslateDoubleSlab(new BID(651, 0 , -396), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new PolishedDeepslateDoubleSlab(new BID(652, 0 , -397), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new DeepslateTileDoubleslab(new BID(653, 0 , -398), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new DeepslateBrickDoubleSlab(new BID(654, 0 , -399), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateLapisOre(new BID(655, 0, -400), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateIronOre(new BID(656, 0, -401), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateGoldOre(new BID(657, 0, -402), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateRedstoneOre(new BID(658, 0, -403), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new LitDeepslateRedstoneOre(new BID(659, 0 , -404), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateDiamondOre(new BID(660, 0, -405), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateCoalOre(new BID(661, 0, -406), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateEmeraldOre(new BID(662, 0, -407), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new DeepslateCopperOre(new BID(663, 0, -408), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new CrackedDeepslateTiles(new BID(664, 0, -409), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new CrackedDeepslateBricks(new BID(665, 0, -410), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new GlowLichen(new BID(666, 0 , -411), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new Candle(new BID(667, 0, -412), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WhiteCandle(new BID(668, 0, -413), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new OrangeCandle(new BID(669, 0, -414), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new MagentaCandle(new BID(670, 0, -415), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new LightBlueCandle(new BID(671, 0, -416), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new YellowCandle(new BID(672, 0, -417), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new LimeCandle(new BID(673, 0, -418), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //elf::registerBlockGlobally(new pink_candle(new BID(674, 0 , -419), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new gray_candle(new BID(675, 0 , -420), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new light_gray_candle(new BID(676, 0 , -421), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new cyan_candle(new BID(677, 0 , -422), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new purple_candle(new BID(678, 0 , -423), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new blue_candle(new BID(679, 0 , -424), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new brown_candle(new BID(680, 0 , -425), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new green_candle(new BID(681, 0 , -426), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new red_candle(new BID(682, 0 , -427), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new black_candle(new BID(683, 0 , -428), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new candle_cake(new BID(684, 0 , -429), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new white_candle_cake(new BID(685, 0 , -430), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new orange_candle_cake(new BID(686, 0 , -431), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new magenta_candle_cake(new BID(687, 0 , -432), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new light_blue_candle_cake(new BID(688, 0 , -433), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new yellow_candle_cake(new BID(689, 0 , -434), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new lime_candle_cake(new BID(690, 0 , -435), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new pink_candle_cake(new BID(691, 0 , -436), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new gray_candle_cake(new BID(692, 0 , -437), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new light_gray_candle_cake(new BID(693, 0 , -438), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new cyan_candle_cake(new BID(694, 0 , -439), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new purple_candle_cake(new BID(695, 0 , -440), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new blueCandleCake(new BID(696, 0 , -441), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new brownCandleCake(new BID(697, 0 , -442), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new greenCandleCake(new BID(698, 0 , -443), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new red_candleCake(new BID(699, 0 , -444), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new black_candleCake(new BID(700, 0 , -445), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedOxidizedCopper(new BID(701, 0, -446), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedOxidizedCutCopper(new BID(702, 0, -447), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedOxidizedCutCopperStairs(new BID(703, 0, -448), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new WaxedOxidizedCutCopperSlab(new BIF(704, [705], 0, -449), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new WaxedOxidizedDoubleCutCopperSlab(new BID(705, 0 , -450), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new RawIronBlock(new BID(706, 0, -451), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new RawCopperBlock(new BID(707, 0, -452), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new RawGoldBlock(new BID(708, 0, -453), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new InfestedDeepslate(new BID(709, 0, -454), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        self::registerBlockGlobally(new Sculk(new BID(713, 0 , -455), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new sculk_vein(new BID(714, 0 , -413), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //elf::registerBlockGlobally(new sculk_catalyst(new BID(715, 0 , -413), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new sculk_shrieker(new BID(716, 0 , -413), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new client_request_placeholder_block(new BID(720, 0 , -413), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new mysterious_frame(new BID(721, 0 , -413), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //self::registerBlockGlobally(new mysterious_frame_slot(new BID(722, 0 , -413), new BlockBreakInfo(3, BlockToolType::PICKAXE, 1)));
        //$blueTorch = ItemFactory::getInstance()->get(204,0);
        //CreativeInventory::getInstance()->add($blueTorch);
        //self::registerBlockInNetworkLayer($blueTorch);
        //$purpleTorch = ItemFactory::getInstance()->get(204,8);
        //CreativeInventory::getInstance()->add($purpleTorch);
        //self::registerBlockInNetworkLayer($purpleTorch);

        //$redTorch = ItemFactory::getInstance()->get(202,0);
        //CreativeInventory::getInstance()->add($redTorch);
        //self::registerBlockInNetworkLayer($redTorch);

        //$greenTorch = ItemFactory::getInstance()->get(202,8);
        //CreativeInventory::getInstance()->add($greenTorch);
        //self::registerBlockInNetworkLayer($greenTorch);
        //ItemFactory::getInstance()->isRegistered(504, 0 );
        ItemFactory::getInstance()->remap(new ItemIdentifier(CustomIds::RAW_COPPER_ITEM, 0),  new Item(new ItemIdentifier(507, 0), "Raw Copper"), true);
        ItemFactory::getInstance()->remap(new ItemIdentifier(CustomIds::RAW_IRON_ITEM, 0),  new Item(new ItemIdentifier(505, 0), "Raw Iron"), true);
        ItemFactory::getInstance()->remap(new ItemIdentifier(CustomIds::RAW_GOLD_ITEM, 0),  new Item(new ItemIdentifier(506, 0), "Raw Gold"), true);
        ItemFactory::getInstance()->remap(new ItemIdentifier(CustomIds::COPPER_INGOT_ITEM, 0),  new Item(new ItemIdentifier(504, 0), "Copper Ingot"), true);
        //ItemFactory::getInstance()->register(new ItemIdentifier(CustomIds::AMETHYST_SHARD, 0),  new Item(new ItemIdentifier(624, 0), "Amethyst Shard"), true);
        //$copperingot = ItemFactory::getInstance()->get(CustomIds::COPPER_INGOT_ITEM);
        //CreativeInventory::getInstance()->add($copperingot);
        /*ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_13, 0), "13 Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_CAT, 0), "Cat Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_BLOCKS, 0), "Blocks Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_CHIRP, 0), "Chirp Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_FAR, 0), "Far Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_MALL, 0), "Mall Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_MELLOHI, 0), "Mellohi Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_STAL, 0), "Stal Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_STRAD, 0), "Strad Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_WARD, 0), "Ward Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_11, 0), "11 Disc"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::RECORD_WAIT, 0), "Wait Disc"), true);
        //CreativeInventory::getInstance()->add();*/

    }
    public function initItems(): void
    {
        $reflectionClass = new ReflectionClass(ItemTranslator::getInstance());
        $property = $reflectionClass->getProperty("simpleCoreToNetMapping");
        $property->setAccessible(true);
        $value = $property->getValue(ItemTranslator::getInstance());

        $property2 = $reflectionClass->getProperty("simpleNetToCoreMapping");
        $property2->setAccessible(true);
        $value2 = $property2->getValue(ItemTranslator::getInstance());

        $runtimeIds = json_decode(file_get_contents(BEDROCK_DATA_PATH . 'required_item_list.json'), true);
        //$itemIds = json_decode(file_get_contents(BEDROCK_DATA_PATH . 'item_id_map.json'), true);
        $itemIds = json_decode(file_get_contents(BEDROCK_DATA_PATH . 'required_item_list.json'), true);
        foreach ([
                     "minecraft:spyglass" => CustomIds::SPYGLASS,
                     "minecraft:copper_ingot" => CustomIds::COPPER_INGOT_ITEM,
                     "minecraft:raw_gold" => CustomIds::RAW_GOLD_ITEM,
                     "minecraft:raw_iron" => CustomIds::RAW_IRON_ITEM,
                     "minecraft:raw_copper" => CustomIds::RAW_COPPER_ITEM,
                     "minecraft:amethyst_shard" => CustomIds::AMETHYST_SHARD,
                     "minecraft:goat_horn" => CustomIds::GOAT_HORN,
                     "minecraft:lightning_rod" => -312
                 ] as $name => $id) {
            $itemIds[$name] = $id;
            $itemId = $itemIds[$name];
            $runtimeId = $runtimeIds[$name]["runtime_id"];
            $value[$itemId] = $runtimeId;
            $value2[$runtimeId] = $itemId;
            $property->setValue(ItemTranslator::getInstance(), $value);
            $property2->setValue(ItemTranslator::getInstance(), $value2);
        }

        $property->setValue(ItemTranslator::getInstance(), $value);
        $property2->setValue(ItemTranslator::getInstance(), $value2);


        $spyglass = new Spyglass();
        ItemFactory::getInstance()->register($spyglass, true);
        $amethystshard = new AmethystShard();
        $elytra = new ElytraItem();
        $firecharge = new FireChargeItem();
        $firework = new FireworkRocketItem();
        ItemFactory::getInstance()->register(new GoatHorn(new ItemIdentifier(CustomIds::GOAT_HORN, 0), "Goat Horn"));
        ItemFactory::getInstance()->register(new GoatHorn(new ItemIdentifier(CustomIds::GOAT_HORN, 1), "Goat Horn"));
        ItemFactory::getInstance()->register(new GoatHorn(new ItemIdentifier(CustomIds::GOAT_HORN, 2), "Goat Horn"));
        ItemFactory::getInstance()->register(new GoatHorn(new ItemIdentifier(CustomIds::GOAT_HORN, 3), "Goat Horn"));
        ItemFactory::getInstance()->register(new GoatHorn(new ItemIdentifier(CustomIds::GOAT_HORN, 4), "Goat Horn"));
        ItemFactory::getInstance()->register(new GoatHorn(new ItemIdentifier(CustomIds::GOAT_HORN, 5), "Goat Horn"));
        ItemFactory::getInstance()->register(new GoatHorn(new ItemIdentifier(CustomIds::GOAT_HORN, 6), "Goat Horn"));
        ItemFactory::getInstance()->register(new GoatHorn(new ItemIdentifier(CustomIds::GOAT_HORN, 7), "Goat Horn"));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::FIREWORKS));
        ItemFactory::getInstance()->register($firework, true);
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::ELYTRA));
        ItemFactory::getInstance()->register($elytra, true);
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::FIRE_CHARGE));
        ItemFactory::getInstance()->register($firecharge, true);
        ItemFactory::getInstance()->register($amethystshard, true);
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::AMETHYST_SHARD));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::SPYGLASS));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::GOAT_HORN, 0));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::GOAT_HORN, 1));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::GOAT_HORN, 2));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::GOAT_HORN, 3));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::GOAT_HORN, 4));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::GOAT_HORN, 5));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::GOAT_HORN, 6));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::GOAT_HORN, 7));

        //ItemFactory::getInstance()->register(new Record(new ItemIdentifier(CustomIds::RECORD_5, 0), CustomIds::ITEM_RECORD_5_DESC, "Record 5"));
        //ItemFactory::getInstance()->register(new ItemBlock(new ItemIdentifier(CustomIds::GLOW_FRAME_ITEM, 0), CustomIds::ITEM_GLOW_FRAME_BLOCK));


        self::register(true);
    }
    public static function item_record_5_desc() : Translatable{
        return new Translatable(CustomIds::ITEM_RECORD_5_DESC, []);
    }



    public static function register(bool $creative = false): bool
    {

        $item = new Spyglass();
        $name = $item->getVanillaName();

        if ($name !== null && StringToItemParser::getInstance()->parse($name) === null) StringToItemParser::getInstance()->register($name, fn() => $item);
        if ($creative && !CreativeInventory::getInstance()->contains($item)) CreativeInventory::getInstance()->add($item);
        return true;
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
        $packet = $event->getPacket();
        if(!$packet instanceof InventoryTransactionPacket){
            return;
        }
        $player = $event->getOrigin()->getPlayer() ?: throw new AssumptionFailedError("Player is not online");
        $trData = $packet->trData;
        $conv = TypeConverter::getInstance();
        switch(true){
            case $trData instanceof UseItemTransactionData:
                $item = $conv->netItemStackToCore($trData->getItemInHand()->getItemStack());
                if($item instanceof Crossbow){
                    $event->cancel();
                    $oldItem = clone $item;
                    $ev = new PlayerItemUseEvent($player, $item, $player->getDirectionVector());
                    if($player->hasItemCooldown($item) || $player->isSpectator()){
                        $ev->cancel();
                    }
                    $ev->call();
                    if($ev->isCancelled()){
                        return;
                    }
                    if($item->onClickAir($player, $player->getDirectionVector())->equals(ItemUseResult::FAIL())){
                        $player->getNetworkSession()->getInvManager()?->syncSlot($player->getInventory(), $player->getInventory()->getHeldItemIndex());
                        return;
                    }
                    $player->resetItemCooldown($item);
                    if(!$item->equalsExact($oldItem) && $oldItem->equalsExact($player->getInventory()->getItemInHand())){
                        $player->getInventory()->setItemInHand($item);
                    }
                    if(!$oldItem->isCharged() && !$item->isCharged()){
                        $player->setUsingItem(true);
                    }else{
                        $player->setUsingItem(false);
                    }
                }
                break;
            case $trData instanceof ReleaseItemTransactionData:
                $item = $player->getInventory()->getItemInHand();
                if($item instanceof Crossbow){
                    $event->cancel();
                    if(!$player->isUsingItem() || $player->hasItemCooldown($item)){
                        return;
                    }
                    if($item->onReleaseUsing($player)->equals(ItemUseResult::SUCCESS())){
                        $player->resetItemCooldown($item);
                        $player->getInventory()->setItemInHand($item);
                    }
                }
                break;
        }
    }

    /**
     * @throws \ReflectionException
     */
    public static function registerBlockGlobally(Block $block): void
    {
        // API Layer
        BlockFactory::getInstance()->register($block);
        CreativeInventory::getInstance()->add($block->asItem());
        StringToItemParser::getInstance()->registerBlock(strtolower(str_replace(' ', '_', $block->getName())), fn() => $block);

        // Network Layer
        $itemId = $block->getIdInfo()->getItemId();

        $itemTranslator = ItemTranslator::getInstance();
        $prop = new \ReflectionProperty($itemTranslator, 'simpleCoreToNetMapping');
        $prop->setAccessible(true);

        $simpleCoreToNetMap = $prop->getValue($itemTranslator);
        $simpleCoreToNetMap[$itemId] = $itemId;
        $prop->setValue($itemTranslator, $simpleCoreToNetMap);

        $prop = new \ReflectionProperty($itemTranslator, 'simpleNetToCoreMapping');
        $prop->setAccessible(true);
        $simpleNetToCoreMap = $prop->getValue($itemTranslator);
        $simpleNetToCoreMap[$itemId] = $itemId;
        $prop->setValue($itemTranslator, $simpleNetToCoreMap);
    }

    /**
     * @throws \ReflectionException
     */
    public static function registerRuntimeIds(): void
    {

        $legacyBlockIdToStringIdMap = LegacyBlockIdToStringIdMap::getInstance();

        $stringToLegacy = new \ReflectionProperty(LegacyToStringBidirectionalIdMap::class, 'stringToLegacy');
        $stringToLegacy->setAccessible(true);
        $stringToLegacyArray = $legacyBlockIdToStringIdMap->getStringToLegacyMap();
        $legacyToString = new \ReflectionProperty(LegacyToStringBidirectionalIdMap::class, 'legacyToString');
        $legacyToString->setAccessible(true);
        $legacyToStringArray = $legacyBlockIdToStringIdMap->getLegacyToStringMap();
        $extraBlocks = [
            "minecraft:sculk_sensor" => 562,
            "minecraft:copper_ore" => 566,
            "minecraft:lightning_rod" => 567,
            "minecraft:dripstone_block" => 572,
            "minecraft:dirt_with_roots" => 573,
            "minecraft:hanging_roots" => 574,
            "minecraft:moss_block" => 575,
            "minecraft:spore_blossom" => 576,
            "minecraft:cave_vines" => 577,
            "minecraft:big_dripleaf" => 578,
            "minecraft:azalea_leaves" => 579,
            "minecraft:azalea_leaves_flowered" => 580,
            "minecraft:calcite" => 581,
            "minecraft:amethyst_block" => 582,
            "minecraft:budding_amethyst" => 583,
            "minecraft:amethyst_cluster" => 584,
            "minecraft:large_amethyst_bud" => 585,
            "minecraft:medium_amethyst_bud" => 586,
            "minecraft:small_amethyst_bud" => 587,
            "minecraft:tuff" => 588,
            "minecraft:tinted_glass" => 589,
            "minecraft:moss_carpet" => 590,
            "minecraft:small_dripleaf_block" => 591,
            "minecraft:azalea" => 592,
            "minecraft:flowering_azalea" => 593,
            "minecraft:glow_frame" => 594,
            "minecraft:copper_block" => 595,
            "minecraft:exposed_copper" => 596,
            "minecraft:weathered_copper" => 597,
            "minecraft:oxidized_copper" => 598,
            "minecraft:waxed_copper" => 599,
            "minecraft:waxed_exposed_copper" => 600,
            "minecraft:waxed_weathered_copper" => 601,
            "minecraft:cut_copper" => 602,
            "minecraft:exposed_cut_copper" => 603,
            "minecraft:weathered_cut_copper" => 604,
            "minecraft:oxidized_cut_copper" => 605,
            "minecraft:waxed_cut_copper" => 606,
            "minecraft:waxed_exposed_cut_copper" => 607,
            "minecraft:waxed_weathered_cut_copper" => 608,
            "minecraft:cut_copper_stairs" => 609,
            "minecraft:exposed_cut_copper_stairs" => 610,
            "minecraft:weathered_cut_copper_stairs" => 611,
            "minecraft:oxidized_cut_copper_stairs" => 612,
            "minecraft:waxed_cut_copper_stairs" => 613,
            "minecraft:waxed_exposed_cut_copper_stairs" => 614,
            "minecraft:waxed_weathered_cut_copper_stairs" => 615,
            "minecraft:cut_copper_slab" => 616,
            "minecraft:exposed_cut_copper_slab" => 617,
            "minecraft:weathered_cut_copper_slab" => 618,
            "minecraft:oxidized_cut_copper_slab" => 619,
            "minecraft:waxed_cut_copper_slab" => 620,
            "minecraft:waxed_exposed_cut_copper_slab" => 621,
            "minecraft:waxed_weathered_cut_copper_slab" => 622,
            "minecraft:double_cut_copper_slab" => 623,
            "minecraft:exposed_double_cut_copper_slab" => 624,
            "minecraft:weathered_double_cut_copper_slab" => 625,
            "minecraft:oxidized_double_cut_copper_slab" => 626,
            "minecraft:waxed_double_cut_copper_slab" => 627,
            "minecraft:waxed_exposed_double_cut_copper_slab" => 628,
            "minecraft:waxed_weathered_double_cut_copper_slab" => 629,
            "minecraft:cave_vines_body_with_berries" => 630,
            "minecraft:cave_vines_head_with_berries" => 631,
            "minecraft:smooth_basalt" => 632,
            "minecraft:deepslate" => 633,
            "minecraft:cobbled_deepslate" => 634,
            "minecraft:cobbled_deepslate_slab" => 635,
            "minecraft:cobbled_deepslate_stairs" => 636,
            "minecraft:cobbled_deepslate_wall" => 637,
            "minecraft:polished_deepslate" => 638,
            "minecraft:polished_deepslate_slab" => 639,
            "minecraft:polished_deepslate_stairs" => 640,
            "minecraft:polished_deepslate_wall" => 641,
            "minecraft:deepslate_tiles" => 642,
            "minecraft:deepslate_tile_slab" => 643,
            "minecraft:deepslate_tile_stairs" => 644,
            "minecraft:deepslate_tile_wall" => 645,
            "minecraft:deepslate_bricks" => 646,
            "minecraft:deepslate_brick_slab" => 647,
            "minecraft:deepslate_brick_stairs" => 648,
            "minecraft:deepslate_brick_wall" => 649,
            "minecraft:chiseled_deepslate" => 650,
            "minecraft:cobbled_deepslate_double_slab" => 651,
            "minecraft:polished_deepslate_double_slab" => 652,
            "minecraft:deepslate_tile_double_slab" => 653,
            "minecraft:deepslate_brick_double_slab" => 654,
            "minecraft:deepslate_lapis_ore" => 655,
            "minecraft:deepslate_iron_ore" => 656,
            "minecraft:deepslate_gold_ore" => 657,
            "minecraft:deepslate_redstone_ore" => 658,
            "minecraft:lit_deepslate_redstone_ore" => 659,
            "minecraft:deepslate_diamond_ore" => 660,
            "minecraft:deepslate_coal_ore" => 661,
            "minecraft:deepslate_emerald_ore" => 662,
            "minecraft:deepslate_copper_ore" => 663,
            "minecraft:cracked_deepslate_tiles" => 664,
            "minecraft:cracked_deepslate_bricks" => 665,
            "minecraft:glow_lichen" => 666,
            "minecraft:candle" => 667,
            "minecraft:white_candle" => 668,
            "minecraft:orange_candle" => 669,
            "minecraft:magenta_candle" => 670,
            "minecraft:light_blue_candle" => 671,
            "minecraft:yellow_candle" => 672,
            "minecraft:lime_candle" => 673,
            "minecraft:pink_candle" => 674,
            "minecraft:gray_candle" => 675,
            "minecraft:light_gray_candle" => 676,
            "minecraft:cyan_candle" => 677,
            "minecraft:purple_candle" => 678,
            "minecraft:blue_candle" => 679,
            "minecraft:brown_candle" => 680,
            "minecraft:green_candle" => 681,
            "minecraft:red_candle" => 682,
            "minecraft:black_candle" => 683,
            "minecraft:candle_cake" => 684,
            "minecraft:white_candle_cake" => 685,
            "minecraft:orange_candle_cake" => 686,
            "minecraft:magenta_candle_cake" => 687,
            "minecraft:light_blue_candle_cake" => 688,
            "minecraft:yellow_candle_cake" => 689,
            "minecraft:lime_candle_cake" => 690,
            "minecraft:pink_candle_cake" => 691,
            "minecraft:gray_candle_cake" => 692,
            "minecraft:light_gray_candle_cake" => 693,
            "minecraft:cyan_candle_cake" => 694,
            "minecraft:purple_candle_cake" => 695,
            "minecraft:blue_candle_cake" => 696,
            "minecraft:brown_candle_cake" => 697,
            "minecraft:green_candle_cake" => 698,
            "minecraft=>red_candle_cake" => 699,
            "minecraft:black_candle_cake" => 700,
            "minecraft:waxed_oxidized_copper" => 701,
            "minecraft:waxed_oxidized_cut_copper" => 702,
            "minecraft:waxed_oxidized_cut_copper_stairs" => 703,
            "minecraft:waxed_oxidized_cut_copper_slab" => 704,
            "minecraft:waxed_oxidized_double_cut_copper_slab" => 705,
            "minecraft:raw_iron_block" => 706,
            "minecraft:raw_copper_block" => 707,
            "minecraft:raw_gold_block" => 708,
            "minecraft:infested_deepslate" => 709,
            "minecraft:sculk" => 713,
            "minecraft:sculk_vein" => 714,
            "minecraft:sculk_catalyst" => 715,
            "minecraft:sculk_shrieker" => 716,
            "minecraft:client_request_placeholder_block" => 720,
            "minecraft:mysterious_frame" => 721,
            "minecraft:mysterious_frame_slot" => 722

        ];
        foreach ($extraBlocks as $stringId => $legacyBlockId) {
            $stringToLegacyArray[$stringId] = $legacyBlockId;
            $stringToLegacy->setValue($legacyBlockIdToStringIdMap, $stringToLegacyArray);
            $legacyToStringArray[$legacyBlockId] = $stringId;
            $legacyToString->setValue($legacyBlockIdToStringIdMap, $legacyToStringArray);
        }
        $runtimeBlockMapping = RuntimeBlockMapping::getInstance();

        $registerMapping = new \ReflectionMethod($runtimeBlockMapping, 'registerMapping');
        $registerMapping->setAccessible(true);
        $metaMap = [];
        foreach ($runtimeBlockMapping->getBedrockKnownStates() as $runtimeId => $state) {
            $legacyId = $blocks[$state->getString('name')] ?? null;
            if ($legacyId === null) continue;

            $metaMap[$legacyId] ??= 0;

            $registerMapping->invoke($runtimeBlockMapping, $runtimeId, $legacyId, $metaMap[$legacyId]++);
        }

        $legacyBlockIdToStringIdMap = LegacyBlockIdToStringIdMap::getInstance();
        $runtimeBlockMapping = RuntimeBlockMapping::getInstance();
        $registerMapping = new \ReflectionMethod($runtimeBlockMapping, 'registerMapping');
        $registerMapping->setAccessible(true);
        $metaMap = [];
        foreach ($runtimeBlockMapping->getBedrockKnownStates() as $runtimeId => $state) {
            $legacyId = $legacyBlockIdToStringIdMap->stringToLegacy($state->getString("name"));
            if ($legacyId === null or $legacyId <= BlockLegacyIds::LIT_BLAST_FURNACE) {
                continue;
            }

            $metaMap[$legacyId] ??= 0;

            $meta = $metaMap[$legacyId]++;
            if ($meta > 15) {
                continue;
            }
            $registerMapping->invoke($runtimeBlockMapping, $runtimeId, $legacyId, $meta);
        }
    }
    private function registerItem(Item $item): void
    {
        ItemFactory::getInstance()->register($item, true);
        if (!CreativeInventory::getInstance()->contains($item)) {
            CreativeInventory::getInstance()->add($item);
        }
        if (true) {
            $name = strtolower($item->getName());
            $name = str_replace(" ", "_", $name);
            //StringToItemParser::getInstance()->register($name, fn() => $item);
        }
    }

    private function registerSlab(Slab $slab): void
    {
        $this->registerBlock($slab);
        $identifierFlattened = $slab->getIdInfo();
        if ($identifierFlattened instanceof BIDFlattened) {
            BlockFactory::getInstance()->remap($identifierFlattened->getSecondId(), $identifierFlattened->getVariant() | 0x1, $slab->setSlabType(SlabType::DOUBLE()));
        }
    }

    private function registerBlock(Block $block, bool $registerToParser = true, bool $addToCreative = true): void
    {
        BlockFactory::getInstance()->register($block, true);
        if ($addToCreative && !CreativeInventory::getInstance()->contains($block->asItem())) {
            CreativeInventory::getInstance()->add($block->asItem());
        }
        if ($registerToParser) {
            $name = strtolower($block->getName());
            $name = str_replace(" ", "_", $name);
            StringToItemParser::getInstance()->registerBlock($name, fn() => $block);
        }
    }

    public function itemblockregister()
    {

        $class = new \ReflectionClass(ToolTier::class);
        $register = $class->getMethod('register');
        $register->setAccessible(true);
        $constructor = $class->getConstructor();
        $constructor->setAccessible(true);
        $instance = $class->newInstanceWithoutConstructor();
        $constructor->invoke($instance, 'netherite', 6, 2031, 9, 10);
        $register->invoke(null, $instance);

        $class1 = new \ReflectionClass(RecordType::class);
        $register1 = $class1->getMethod('register');
        $register1->setAccessible(true);
        $constructor1 = $class1->getConstructor();
        $constructor1->setAccessible(true);
        $instance1 = $class1->newInstanceWithoutConstructor();
        $constructor1->invoke($instance1, 'disk_pigstep', 'Lena Raine - Pigstep', CustomIds::RECORD_PIGSTEP_SOUND_ID, new Translatable('item.record_pigstep.desc', []));
        //$constructor->invoke($instance, 'disk_otherside ', 'Lena Raine - Otherside ', CustomIds::RECORD_OTHERSIDE_SOUND_ID, new Translatable('item.record_otherside.desc', []));
        $register1->invoke(null, $instance1);

        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::ITEM_NETHERITE_INGOT, 0), 'Netherite Ingot'), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::ITEM_NETHERITE_SCRAP, 0), 'Netherite Scrap'), true);
        ItemFactory::getInstance()->register(new Sword(new ItemIdentifier(CustomIds::ITEM_NETHERITE_SWORD, 0), 'Netherite Sword', ToolTier::NETHERITE()), true);
        ItemFactory::getInstance()->register(new Shovel(new ItemIdentifier(CustomIds::ITEM_NETHERITE_SHOVEL, 0), 'Netherite Shovel', ToolTier::NETHERITE()), true);
        ItemFactory::getInstance()->register(new Pickaxe(new ItemIdentifier(CustomIds::ITEM_NETHERITE_PICKAXE, 0), 'Netherite Pickaxe', ToolTier::NETHERITE()), true);
        ItemFactory::getInstance()->register(new Axe(new ItemIdentifier(CustomIds::ITEM_NETHERITE_AXE, 0), 'Netherite Axe', ToolTier::NETHERITE()), true);
        ItemFactory::getInstance()->register(new Hoe(new ItemIdentifier(CustomIds::ITEM_NETHERITE_HOE, 0), 'Netherite Hoe', ToolTier::NETHERITE()), true);
        ItemFactory::getInstance()->register(new Armor(new ItemIdentifier(CustomIds::NETHERITE_HELMET, 0), 'Netherite Helmet', new ArmorTypeInfo(6, 407, ArmorInventory::SLOT_HEAD)), true);
        ItemFactory::getInstance()->register(new Armor(new ItemIdentifier(CustomIds::NETHERITE_CHESTPLATE, 0), 'Netherite Chestplate', new ArmorTypeInfo(3, 592, ArmorInventory::SLOT_CHEST)), true);
        ItemFactory::getInstance()->register(new Armor(new ItemIdentifier(CustomIds::NETHERITE_LEGGINGS, 0), 'Netherite Leggings', new ArmorTypeInfo(3, 481, ArmorInventory::SLOT_LEGS)), true);
        ItemFactory::getInstance()->register(new Armor(new ItemIdentifier(CustomIds::NETHERITE_BOOTS, 0), 'Netherite Boots', new ArmorTypeInfo(6, 555, ArmorInventory::SLOT_FEET)), true);
        StringToItemParser::getInstance()->register('netherite_ingot', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT));
        StringToItemParser::getInstance()->register('netherite_scrap', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_SCRAP));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_SCRAP));
        StringToItemParser::getInstance()->register('netherite_sword', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_SWORD));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_SWORD));
        StringToItemParser::getInstance()->register('netherite_shovel', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_SHOVEL));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_SHOVEL));
        StringToItemParser::getInstance()->register('netherite_pickaxe', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_PICKAXE));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_PICKAXE));
        StringToItemParser::getInstance()->register('netherite_axe', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_AXE));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_AXE));
        StringToItemParser::getInstance()->register('netherite_hoe', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_HOE));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_HOE));

        StringToItemParser::getInstance()->register('netherite_helmet', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::NETHERITE_HELMET));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::NETHERITE_HELMET));
        StringToItemParser::getInstance()->register('netherite_chestplate', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::NETHERITE_CHESTPLATE));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::NETHERITE_CHESTPLATE));
        StringToItemParser::getInstance()->register('netherite_leggings', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::NETHERITE_LEGGINGS));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::NETHERITE_LEGGINGS));
        StringToItemParser::getInstance()->register('netherite_boots', fn(string $input) => ItemFactory::getInstance()->get(CustomIds::NETHERITE_BOOTS));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::NETHERITE_BOOTS));


        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::RAPID_FERTILIZER));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::TURTLE_SHELL_PIECE));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::TURTLE_HELMET));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::TURTLE_EGG));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::CROSSBOW));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::SADDLE));
        //CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::FIREWORKS));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::LEAD));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::LODESTONE_COMPASS));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::BEE_NEST));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::BEEHIVE));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::HONEY_BLOCK_ITEM));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::HONEYCOMB_BLOCK_ITEM));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::HONEYCOMB));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::HONEY_BOTTLE));


        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::HONEYCOMB, 0), "Honeycomb"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::HONEY_BOTTLE, 0), "Honey Bottle"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(CustomIds::SPYGLASS, 0), "Spyglass"), true);
        //"minecraft:turtle_shell_piece": 468,
        //"minecraft:turtle_helmet": 469,
        //449 is Superdünger
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(ItemIds::RAPID_FERTILIZER, 0), "Rapid Fertilizer"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(ItemIds::TURTLE_SHELL_PIECE, 0), "Turtle Shell Piece"), true);
        ItemFactory::getInstance()->register(new Armor(new ItemIdentifier(ItemIds::TURTLE_HELMET, 0), "Turtle Helmet", new ArmorTypeInfo(1, 276, ArmorInventory::SLOT_HEAD)), true);
        ItemFactory::getInstance()->register(new Shield(new ItemIdentifier(ItemIds::SHIELD, 0), "Shield"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(ItemIds::ELYTRA, 0), "Elytra"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(ItemIds::LEAD, 0), "Lead"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(ItemIds::ENDER_EYE, 0), "Ender Eye"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(ItemIds::SADDLE, 0), "Saddle"), true);
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(ItemIds::FIREWORKS, 0), "Fireworks"), true);
        ItemFactory::getInstance()->register(new FlintAndSteel(new ItemIdentifier(ItemIds::FLINT_AND_STEEL, 0), "Flint and Steel"), true);
        ItemFactory::getInstance()->register(new Record(new ItemIdentifier(CustomIds::RECORD_PIGSTEP, 0), RecordType::DISK_PIGSTEP(), "Record Pigstep"), true);
        ItemFactory::getInstance()->register($crossbow = new Crossbow(new ItemIdentifier(ItemIds::CROSSBOW, 0), "Crossbow"), true);
        ItemFactory::getInstance()->register(new Trident(new ItemIdentifier(ItemIds::TRIDENT, 0), "Trident"));
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(ItemIds::FIREWORKS_CHARGE, 0), "Fireworks Charge")); //ITEM
        ItemFactory::getInstance()->register(new Item(new ItemIdentifier(ItemIds::CARROT_ON_A_STICK, 0), "Carrot on a Stick")); //ITEM

        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::SHIELD));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(CustomIds::RECORD_PIGSTEP));
        CreativeInventory::getInstance()->add(ItemFactory::getInstance()->get(ItemIds::TRIDENT));
        CreativeInventory::getInstance()->add($crossbow);
        $class2 = new \ReflectionClass(TreeType::class);
        $register2 = $class2->getMethod('register');
        $register2->setAccessible(true);
        $constructor2 = $class2->getConstructor();
        $constructor2->setAccessible(true);
        $instance2 = $class2->newInstanceWithoutConstructor();
        $constructor2->invoke($instance2, 'crimson', 'Crimson', 6);
        $register2->invoke(null, $instance2);

        $instance2 = $class2->newInstanceWithoutConstructor();
        $constructor2->invoke($instance2, 'warped', 'Warped', 7);
        $register2->invoke(null, $instance2);

        $bf = BlockFactory::getInstance();
        //Defaults
        $bf->register(new FlowerPot(new BID(IDS::FLOWER_POT_BLOCK, 0, ItemIds::FLOWER_POT), "Flower Pot", new BlockBreakInfo(0, BlockToolType::NONE, 0, 0)), true);

        $bf->register(new IronOre(new BID(IDS::IRON_ORE,0, ItemIds::IRON_ORE),"Iron Ore" , new BlockBreakInfo(3, BlockToolType::PICKAXE, 3, 3)), true);
        $bf->register(new GoldOre(new BID(IDS::GOLD_ORE,0, ItemIds::GOLD_ORE),"Gold Ore" , new BlockBreakInfo(3, BlockToolType::PICKAXE, 4,3)),true);
        //Bedrock 1.11
        //$bf->register(new Campfire(new BID(Ids::CAMPFIRE, 0, CustomIds::CAMPFIRE_ITEM, TileCampfire::class), "Campfire", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10)));
        $bf->register($composter = new Composter(new BID(IDS::COMPOSTER, 0, ItemIds::COMPOSTER)));
        //CreativeInventory::getInstance()->add($composter);
        $bf->register(new Lectern(new BID(IDS::LECTERN, 0, ItemIds::LECTERN), "Lectern", new BlockBreakInfo(2.5, BlockToolType::AXE, 0, 2.5)), true);
        $bf->register(new Stonecutter(new BID(IDS::STONECUTTER_BLOCK, 0, ItemIds::STONECUTTER), "Stonecutter", new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3.5)), true);
        $bf->register(new Dropper(new BID(IDS::DROPPER, 0, ItemIds::DROPPER), "Dropper", new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3.5)), true);
        $bf->register(new Dispenser(new BID(IDS::DISPENSER, 0, ItemIds::DISPENSER), "Dispenser", new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3.5)), true);

        //Bedrock 1.13
        $bf->register(new Roots(new BID(CustomIds::WHITER_ROSE, 0, CustomIds::WHITER_ROSE_ITEM), "Whiter Rose", BlockBreakInfo::instant()), true);

        //Bedrock 1.14
        //$bf->register(new BeeNest(new BID(CustomIds::BEE_NEST_BLOCK, 0, CustomIds::BEE_NEST), "Bee Nest", new BlockBreakInfo(0.3, BlockToolType::AXE, 0.3)), true);
        //$bf->register(new Beehive(new BID(CustomIds::BEEHIVE_BLOCK, 0, CustomIds::BEEHIVE), "Bee Hive", new BlockBreakInfo(0.6, BlockToolType::AXE, 0.6)), true);
        $bf->register(new HoneyBlock(new BID(CustomIds::HONEY_BLOCK, 0, CustomIds::HONEY_BLOCK_ITEM), "Honey Block", new BlockBreakInfo(0)), true);
        $bf->register(new HoneyCombBlock(new BID(CustomIds::HONEYCOMB_BLOCK, 0, CustomIds::HONEYCOMB_BLOCK_ITEM), "Honeycomb Block", new BlockBreakInfo(0.6)), true);
        //Nether Update 1.16
        $signBreakInfo = new BlockBreakInfo(1.0, BlockToolType::AXE);
        $this->registerBlock(new FloorSign(new BID(CustomIds::CRIMSON_FLOOR_SIGN_BLOCK, 0, CustomIds::CRIMSON_SIGN_ITEM, TileSign::class), "Crimson Floor Sign", $signBreakInfo), true, false);
        $this->registerBlock(new WallSign(new BID(CustomIds::CRIMSON_WALL_SIGN_BLOCK, 0, CustomIds::CRIMSON_SIGN_ITEM, TileSign::class), "Crimson Wall Sign", $signBreakInfo), true, false);
        $this->registerBlock(new FloorSign(new BID(CustomIds::WARPED_FLOOR_SIGN_BLOCK, 0, CustomIds::WARPED_SIGN_ITEM, TileSign::class), "Warped Floor Sign", $signBreakInfo), true, false);
        $this->registerBlock(new WallSign(new BID(CustomIds::WARPED_WALL_SIGN_BLOCK, 0, CustomIds::WARPED_SIGN_ITEM, TileSign::class), "Warped Wall Sign", $signBreakInfo), true, false);
        $this->registerBlock(new Door(new BID(CustomIds::CRIMSON_DOOR_BLOCK, 0, CustomIds::CRIMSON_DOOR_ITEM), "Crimson Door", new BlockBreakInfo(3, BlockToolType::AXE)), false);
        $this->registerBlock(new Door(new BID(CustomIds::WARPED_DOOR_BLOCK, 0, CustomIds::WARPED_DOOR_ITEM), "Warped Door", new BlockBreakInfo(3, BlockToolType::AXE)), false);
        $this->registeritem(new ItemBlockWallOrFloor(new ItemIdentifier(CustomIds::CRIMSON_SIGN_ITEM, 0),BlockFactory::getInstance()->get(CustomIds::CRIMSON_FLOOR_SIGN_BLOCK, 0), BlockFactory::getInstance()->get(CustomIds::CRIMSON_WALL_SIGN_BLOCK, 0)), false);
        $this->registeritem(new ItemBlockWallOrFloor(new ItemIdentifier(CustomIds::WARPED_SIGN_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::WARPED_FLOOR_SIGN_BLOCK, 0), BlockFactory::getInstance()->get(CustomIds::WARPED_WALL_SIGN_BLOCK, 0)), false);

        $this->registeritem(new ItemBlock(new ItemIdentifier(CustomIds::CRIMSON_DOOR_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::CRIMSON_DOOR_BLOCK, 0)));
        $this->registeritem(new ItemBlock(new ItemIdentifier(CustomIds::WARPED_DOOR_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::WARPED_DOOR_BLOCK, 0)));
        $this->registeritem(new ItemBlock(new ItemIdentifier(CustomIds::CAMPFIRE_ITEM, 0), BlockFactory::getInstance()->get(Ids::CAMPFIRE, 0)));
        $this->registeritem(new ItemBlock(new ItemIdentifier(CustomIds::SOUL_CAMPFIRE_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::SOUL_CAMPFIRE_BLOCK, 0)));
        $this->registerItem(new ItemBlock(new ItemIdentifier(CustomIds::CHAIN_ITEM, 0), BlockFactory::getInstance()->get(CustomIds::CHAIN_BLOCK, 0)));
        $this->registeritem(new LodestoneCompass(new ItemIdentifier(CustomIds::LODESTONE_COMPASS, 0)));
        $this->registerBlock(new Campfire(new BID(Ids::CAMPFIRE, 0, CustomIds::CAMPFIRE_ITEM, TileCampfire::class), "Campfire", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10)), false, false);
        $this->registerBlock(new SoulCampfire(new BID(CustomIds::SOUL_CAMPFIRE_BLOCK, 0, CustomIds::SOUL_CAMPFIRE_ITEM, TileCampfire::class), "Soul Campfire", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10)), false, false);
        $this->registerBlock(new Stair(new BID(CustomIds::BLACKSTONE_STAIRS_BLOCK, 0, CustomIds::BLACKSTONE_STAIRS_ITEM), "Blackstone Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
        $this->registerBlock(new Stair(new BID(CustomIds::CRIMSON_STAIRS_BLOCK, 0, CustomIds::CRIMSON_STAIRS_ITEM), "Crimson Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
        $this->registerBlock(new Stair(new BID(CustomIds::POLISHED_BLACKSTONE_STAIRS_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_STAIRS_ITEM), "Polished Blackstone Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
        $this->registerBlock(new Stair(new BID(CustomIds::WARPED_STAIRS_BLOCK, 0, CustomIds::WARPED_STAIRS_ITEM), "Warped Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
        $this->registerBlock(new Stair(new BID(CustomIds::POLISHED_BLACKSTONE_BRICK_STAIRS_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_BRICK_STAIRS_ITEM), "Polished Blackstone Brick Stairs", new BlockBreakInfo(3, BlockToolType::AXE, 0, 6)));
        $this->registerBlock(new Opaque(new BID(CustomIds::ANCIENT_DEBRIS_BLOCK, 0, CustomIds::ANCIENT_DEBRIS_ITEM), "Ancient Debris", new BlockBreakInfo(30, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)));
        $this->registerBlock(new Basalt(new BID(CustomIds::BASALT_BLOCK, 0, CustomIds::BASALT_ITEM), "Basalt", new BlockBreakInfo(1.25, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.2)));
        $this->registerBlock(new PolishedBasalt(new BID(CustomIds::POLISHED_BASALT_BLOCK, 0, CustomIds::POLISHED_BASALT_ITEM), "Polished Basalt", new BlockBreakInfo(1.25, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 4.2)));
        $this->registerBlock(new Fungus(new BID(CustomIds::FUNGUS_BLOCK, 0, CustomIds::FUNGUS_ITEM), "Crimson Fungus", BlockBreakInfo::instant()));
        $this->registerBlock(new WarpedFungus(new BID(CustomIds::WARPED_FUNGUS_BLOCK, 0, CustomIds::WARPED_FUNGUS_ITEM), "Warped Fungus", BlockBreakInfo::instant()));
        $this->registerBlock(new SoulSoil(new BID(CustomIds::SOUL_SOIL_BLOCK, 0, CustomIds::SOUL_SOIL_ITEM), "Soul Soil", new BlockBreakInfo(0.5, BlockToolType::SHOVEL)));
        $this->registerBlock(new SoulFire(new BID(CustomIds::SOUL_FIRE_BLOCK, 0, CustomIds::SOUL_FIRE_ITEM), "Soul Fire", BlockBreakInfo::instant()), true, false);
        $this->registerBlock(new Nylium(new BID(CustomIds::NYLIUM_BLOCK, 0, CustomIds::NYLIUM_ITEM), "Crimson Nylium", new BlockBreakInfo(1, BlockToolType::PICKAXE)));
        $this->registerBlock(new WarpedNylium(new BID(CustomIds::WARPED_NYLIUM_BLOCK, 0, CustomIds::WARPED_NYLIUM_ITEM), "Warped Nylium", new BlockBreakInfo(1, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
        $this->registerBlock(new Opaque(new BID(CustomIds::NETHERITE_BLOCK, 0, CustomIds::NETHERITE_BLOCK_ITEM), "Netherite Block", new BlockBreakInfo(50, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)));
        //$this->registerBlock(new NetherSprouts(new BID(CustomIds::NETHER_SPROUTS_BLOCK, 0, CustomIds::NETHER_SPROUTS_ITEM), "Nether Sprouts", BlockBreakInfo::instant(BlockToolType::SHEARS)));
        $this->registerBlock(new Shroomlight(new BID(CustomIds::SHROOMLIGHT_BLOCK, 0, CustomIds::SHROOMLIGHT_ITEM), "Shroomlight", new BlockBreakInfo(1, BlockToolType::HOE)));
        $this->registerBlock(new SoulLantern(new BID(CustomIds::SOUL_LANTERN_BLOCK, 0, CustomIds::SOUL_LANTERN_ITEM), "Soul Lantern", new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3.5)));
        $this->registerBlock(new SoulTorch(new BID(CustomIds::SOUL_TORCH_BLOCK, 0, CustomIds::SOUL_TORCH_ITEM), "Soul Torch", BlockBreakInfo::instant()));
        $this->registerBlock(new WarpedWartBlock(new BID(CustomIds::WARPED_WART_BLOCK, 0, CustomIds::WARPED_WART_ITEM), "Warped Wart Block", new BlockBreakInfo(1, BlockToolType::HOE, 0, 5)));
        $this->registerBlock(new CryingObsidian(new BID(CustomIds::CRYING_OBSIDIAN_BLOCK, 0, CustomIds::CRYING_OBSIDIAN_ITEM), "Crying Obsidian", new BlockBreakInfo(50, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)));
        //$this->registerBlock(new Target(new BID(CustomIds::TARGET_BLOCK, 0, CustomIds::TARGET_ITEM), "Target", BlockBreakInfo::instant(BlockToolType::HOE)));
        $this->registerBlock(new NetherGoldOre(new BID(CustomIds::NETHER_GOLD_ORE_BLOCK, 0, CustomIds::NETHER_GOLD_ORE_ITEM), "Nether Gold Ore", new BlockBreakInfo(3, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 3)));
        $this->registerBlock(new RespawnAnchor(new BID(CustomIds::RESPAWN_ANCHOR_BLOCK, 0, CustomIds::RESPAWN_ANCHOR_ITEM), "Respawn Anchor", new BlockBreakInfo(50, BlockToolType::PICKAXE, ToolTier::DIAMOND()->getHarvestLevel(), 6000)));
        $blackstoneBreakInfo = new BlockBreakInfo(1.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6);
        $this->registerBlock(new Blackstone(new BID(CustomIds::BLACKSTONE_BLOCK, 0, CustomIds::BLACKSTONE_ITEM), "Blackstone", $blackstoneBreakInfo));
        $this->registerBlock(new PolishedBlackStone(new BID(CustomIds::POLISHED_BLACKSTONE_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_ITEM), "Polished Blackstone", $blackstoneBreakInfo));
        $this->registerBlock(new ChiseledPolishedBlackstone(new BID(CustomIds::CHISELED_POLISHED_BLACKSTONE_BLOCK, 0, CustomIds::CHISELED_POLISHED_BLACKSTONE_ITEM), "Chiseled Polished Blackstone", $blackstoneBreakInfo));
        $this->registerBlock(new GildedBlackstone(new BID(CustomIds::GILDED_BLACKSTONE_BLOCK, 0, CustomIds::GILDED_BLACKSTONE_ITEM), "Gilded Blackstone", $blackstoneBreakInfo));
        $this->registerBlock(new Opaque(new BID(CustomIds::POLISHED_BLACKSTONE_BRICKS_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_BRICKS_ITEM), "Polished Blackstone Bricks", $blackstoneBreakInfo));
        $this->registerBlock(new Opaque(new BID(CustomIds::CRACKED_POLISHED_BLACKSTONE_BRICKS_BLOCK, 0, CustomIds::CRACKED_POLISHED_BLACKSTONE_BRICKS_ITEM), "Cracked Polished Blackstone Bricks", $blackstoneBreakInfo));
        $this->registerSlab(new Slab(new BIDFlattened(CustomIds::BLACKSTONE_SLAB_BLOCK, [CustomIds::BLACKSTONE_DOUBLE_SLAB], 0, CustomIds::BLACKSTONE_SLAB_ITEM), "Blackstone Slab", $blackstoneBreakInfo));
        $this->registerSlab(new Slab(new BIDFlattened(CustomIds::POLISHED_BLACKSTONE_SLAB_BLOCK, [CustomIds::POLISHED_BLACKSTONE_DOUBLE_SLAB], 0, CustomIds::POLISHED_BLACKSTONE_SLAB_ITEM), "Polished Blackstone Slab", $blackstoneBreakInfo));
        $this->registerSlab(new Slab(new BIDFlattened(CustomIds::POLISHED_BLACKSTONE_BRICK_SLAB_BLOCK, [CustomIds::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB], 0, CustomIds::POLISHED_BLACKSTONE_BRICK_SLAB_ITEM), "Polished Blackstone Brick Slab", $blackstoneBreakInfo));
        $this->registerBlock(new StonePressurePlate(new BID(CustomIds::POLISHED_BLACKSTONE_PRESSURE_PLATE_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_PRESSURE_PLATE_ITEM), "Polished Blackstone Pressure Plate", new BlockBreakInfo(0.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
        $this->registerBlock(new PolishedBlackstoneButton(new BID(CustomIds::POLISHED_BLACKSTONE_BUTTON_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_BUTTON_ITEM), "Polished Blackstone Button", new BlockBreakInfo(0.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel())));
        $wallBreakInfo = new BlockBreakInfo(2.0, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 30.0);
        $this->registerBlock(new Wall(new BID(CustomIds::BLACKSTONE_WALL_BLOCK, 0, CustomIds::BLACKSTONE_WALL_ITEM), "Blackstone Wall", $wallBreakInfo));
        $this->registerBlock(new Wall(new BID(CustomIds::POLISHED_BLACKSTONE_WALL_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_WALL_ITEM), "Polished Blackstone Wall", $wallBreakInfo));
        $this->registerBlock(new Wall(new BID(CustomIds::POLISHED_BLACKSTONE_BRICK_WALL_BLOCK, 0, CustomIds::POLISHED_BLACKSTONE_BRICK_WALL_ITEM), "Polished Blackstone Brick Wall", $wallBreakInfo));
        $this->registerBlock(new Chain(new BID(CustomIds::CHAIN_BLOCK, 0, CustomIds::CHAIN_BLOCK_ITEM), "Chain", new BlockBreakInfo(5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel(), 6)), false, false);
        $this->registerBlock(new TwistingVines(new BID(CustomIds::TWISTING_VINES_BLOCK, 0, CustomIds::TWISTING_VINES_ITEM), "Twisting Vines", BlockBreakInfo::instant()));
        $this->registerBlock(new WeepingVines(new BID(CustomIds::WEEPING_VINES_BLOCK, 0, CustomIds::WEEPING_VINES_ITEM), "Weeping Vines", BlockBreakInfo::instant()));
        $this->registerBlock(new Roots(new BID(CustomIds::CRIMSON_ROOTS_BLOCK, 0, CustomIds::CRIMSON_ROOTS_ITEM), "Crimson Roots", BlockBreakInfo::instant()));
        $this->registerBlock(new Roots(new BID(CustomIds::WARPED_ROOTS_BLOCK, 0, CustomIds::WARPED_ROOTS_ITEM), "Warped Roots", BlockBreakInfo::instant()));
        $planksBreakInfo = new BlockBreakInfo(2.0, BlockToolType::AXE, 0, 15.0);
        $this->registerBlock(new Planks(new BID(CustomIds::CRIMSON_PLANKS_BLOCK, 0, CustomIds::CRIMSON_PLANKS_ITEM), "Crimson Planks", $planksBreakInfo));
        $this->registerBlock(new Planks(new BID(CustomIds::WARPED_PLANKS_BLOCK, 0, CustomIds::WARPED_PLANKS_ITEM), "Warped Planks", $planksBreakInfo));
        $this->registerSlab(new Slab(new BIDFlattened(CustomIds::CRIMSON_SLAB_BLOCK, [CustomIds::CRIMSON_DOUBLE_SLAB], 0, CustomIds::CRIMSON_SLAB_ITEM), "Crimson Slab", $planksBreakInfo));
        $this->registerSlab(new Slab(new BIDFlattened(CustomIds::WARPED_SLAB_BLOCK, [CustomIds::WARPED_DOUBLE_SLAB], 0, CustomIds::WARPED_SLAB_ITEM), "Warped Slab", $planksBreakInfo));
        $this->registerBlock(new Log(new BID(CustomIds::CRIMSON_STEM_BLOCK, 0, CustomIds::CRIMSON_STEM_ITEM), "Crimson Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), false));
        $this->registerBlock(new Log(new BID(CustomIds::WARPED_STEM_BLOCK, 0, CustomIds::WARPED_STEM_ITEM), "Warped Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), false));
        $this->registerBlock(new Log(new BID(CustomIds::CRIMSON_STRIPPED_STEM_BLOCK, 0, CustomIds::CRIMSON_STRIPPED_STEM_ITEM), "Crimson Stripped Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), true));
        $this->registerBlock(new Log(new BID(CustomIds::WARPED_STRIPPED_STEM_BLOCK, 0, CustomIds::WARPED_STRIPPED_STEM_ITEM), "Warped Stripped Stem", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), true));
        $this->registerBlock(new Hyphae(new BID(CustomIds::CRIMSON_HYPHAE_BLOCK, 0, CustomIds::CRIMSON_HYPHAE_ITEM), "Crimson Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), false));
        $this->registerBlock(new Hyphae(new BID(CustomIds::WARPED_HYPHAE_BLOCK, 0, CustomIds::WARPED_HYPHAE_ITEM), "Warped Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), false));
        $this->registerBlock(new Hyphae(new BID(CustomIds::CRIMSON_STRIPPED_HYPHAE_BLOCK, 0, CustomIds::CRIMSON_STRIPPED_HYPHAE_ITEM), "Crimson Stripped Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::CRIMSON(), true));
        $this->registerBlock(new Hyphae(new BID(CustomIds::WARPED_STRIPPED_HYPHAE_BLOCK, 0, CustomIds::WARPED_STRIPPED_HYPHAE_ITEM), "Warped Stripped Hyphae", new BlockBreakInfo(2, BlockToolType::AXE, 0, 10), TreeType::WARPED(), true));
       $this->registerBlock(new Fence(new BID(CustomIds::CRIMSON_FENCE_BLOCK, 0, CustomIds::CRIMSON_FENCE_ITEM), "Crimson Fence", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)));
        $this->registerBlock(new Fence(new BID(CustomIds::WARPED_FENCE_BLOCK, 0, CustomIds::WARPED_FENCE_ITEM), "Warped Fence", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)));
        $this->registerBlock(new FenceGate(new BID(CustomIds::CRIMSON_FENCE_GATE_BLOCK, 0, CustomIds::CRIMSON_FENCE_GATE_ITEM), "Crimson Fence Gate", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)));
        $this->registerBlock(new FenceGate(new BID(CustomIds::WARPED_FENCE_GATE_BLOCK, 0, CustomIds::WARPED_FENCE_GATE_ITEM), "Warped Fence Gate", new BlockBreakInfo(2, BlockToolType::AXE, 0, 3)));
        $this->registerBlock(new WoodenTrapdoor(new BID(CustomIds::CRIMSON_TRAPDOOR_BLOCK, 0, CustomIds::CRIMSON_TRAPDOOR_ITEM), "Crimson Trapdoor", new BlockBreakInfo(3, BlockToolType::AXE, 0, 15)));
        $this->registerBlock(new WoodenTrapdoor(new BID(CustomIds::WARPED_TRAPDOOR_BLOCK, 0, CustomIds::WARPED_TRAPDOOR_ITEM), "Warped Trapdoor", new BlockBreakInfo(3, BlockToolType::AXE, 0, 15)));
         $woodenButtonBreakInfo = new BlockBreakInfo(0.5, BlockToolType::AXE);
        $this->registerBlock(new WoodenButton(new BID(CustomIds::CRIMSON_BUTTON_BLOCK, 0, CustomIds::CRIMSON_BUTTON_ITEM), "Crimson Button", $woodenButtonBreakInfo));
        $this->registerBlock(new WoodenButton(new BID(CustomIds::WARPED_BUTTON_BLOCK, 0, CustomIds::WARPED_BUTTON_ITEM), "Warped Button", $woodenButtonBreakInfo));
        $pressurePlateBreakInfo = new BlockBreakInfo(0.5, BlockToolType::AXE);
        $this->registerBlock(new WoodenPressurePlate(new BID(CustomIds::CRIMSON_PRESSURE_PLATE_BLOCK, 0), "Crimson Pressure Plate", $pressurePlateBreakInfo));
        $this->registerBlock(new WoodenPressurePlate(new BID(CustomIds::WARPED_PRESSURE_PLATE_BLOCK, 0), "Warped Pressure Plate", $pressurePlateBreakInfo));
    }
    public function furnace()
    {
        //Furnace
        $furnaceRecipe = new FurnaceRecipe(
            ItemFactory::getInstance()->get(CustomIds::COPPER_INGOT_ITEM),
            ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_ITEM),
        );
        $furnaceRecipe1 = new FurnaceRecipe(
            ItemFactory::getInstance()->get(ItemIds::IRON_INGOT),
            ItemFactory::getInstance()->get(CustomIds::RAW_IRON_ITEM),
        );
        $furnaceRecipe2 = new FurnaceRecipe(
            ItemFactory::getInstance()->get(ItemIds::GOLD_INGOT),
            ItemFactory::getInstance()->get(CustomIds::RAW_GOLD_ITEM),
        );
        $furnaceRecipe3 = new FurnaceRecipe(
            ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_TILES),
            ItemFactory::getInstance()->get(CustomIds::CRACKED_DEEPSLATE_TILES),
        );
        $furnaceRecipe4 = new FurnaceRecipe(
            ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICKS),
            ItemFactory::getInstance()->get(CustomIds::CRACKED_DEEPSLATE_BRICKS),
        );
        $manager = $this->getServer()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::FURNACE());
        $manager->register($furnaceRecipe);
        $manager->register($furnaceRecipe1);
        $manager->register($furnaceRecipe2);
        $manager->register($furnaceRecipe3);
        $manager->register($furnaceRecipe4);

        //Blast Furnace
        $blastfurnaceRecipe = new FurnaceRecipe(
            ItemFactory::getInstance()->get(CustomIds::COPPER_INGOT_ITEM),
            ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_ITEM),
        );
        $blastfurnaceRecipe1 = new FurnaceRecipe(
            ItemFactory::getInstance()->get(ItemIds::IRON_INGOT),
            ItemFactory::getInstance()->get(CustomIds::RAW_IRON_ITEM),
        );
        $blastfurnaceRecipe2 = new FurnaceRecipe(
            ItemFactory::getInstance()->get(ItemIds::GOLD_INGOT),
            ItemFactory::getInstance()->get(CustomIds::RAW_GOLD_ITEM),
        );
        $blastfurnaceRecipe3 = new FurnaceRecipe(
            ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_TILES),
            ItemFactory::getInstance()->get(CustomIds::CRACKED_DEEPSLATE_TILES),
        );
        $blastfurnaceRecipe4 = new FurnaceRecipe(
            ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICKS),
            ItemFactory::getInstance()->get(CustomIds::CRACKED_DEEPSLATE_BRICKS),
        );
        $manager1 = $this->getServer()->getCraftingManager()->getFurnaceRecipeManager(FurnaceType::BLAST_FURNACE());
        $manager1->register($blastfurnaceRecipe);
        $manager1->register($blastfurnaceRecipe1);
        $manager1->register($blastfurnaceRecipe2);
        $manager1->register($blastfurnaceRecipe3);
        $manager1->register($blastfurnaceRecipe4);
    }

    public function crafting()
    {
        //Honey
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'AA ',
                    'AA ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::HONEYCOMB_BLOCK_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'BBB',
                    'AAA',
                    'BBB'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB), 'B' => ItemFactory::getInstance()->get(ItemIds::PLANKS)],
                [ItemFactory::getInstance()->get(CustomIds::BEEHIVE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'AA ',
                    'AA ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::HONEY_BOTTLE)],
                [ItemFactory::getInstance()->get(CustomIds::HONEY_BLOCK_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'BBB',
                    'AAA',
                    'BBB'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB), 'B' => ItemFactory::getInstance()->get(ItemIds::PLANKS)],
                [ItemFactory::getInstance()->get(CustomIds::BEEHIVE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'AAB',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::GLASS_BOTTLE), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEY_BLOCK_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::HONEY_BOTTLE, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::HONEY_BOTTLE)],
                [ItemFactory::getInstance()->get(ItemIds::SUGAR, 0, 3)])
        );

        //Nether and More
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    ' B ',
                    'BAB',
                    'CCC'
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::COAL), 'B' => ItemFactory::getInstance()->get(ItemIds::STICK), 'C' => ItemFactory::getInstance()->get(ItemIds::LOG)],
                [ItemFactory::getInstance()->get(CustomIds::CAMPFIRE_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    ' B ',
                    'BAB',
                    'CCC'
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::SOUL_SAND), 'B' => ItemFactory::getInstance()->get(ItemIds::STICK), 'C' => ItemFactory::getInstance()->get(ItemIds::LOG)],
                [ItemFactory::getInstance()->get(CustomIds::SOUL_CAMPFIRE_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::CRIMSON_STEM_ITEM)], //crimson stem
                [ItemFactory::getInstance()->get(CustomIds::CRIMSON_PLANKS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::WARPED_STEM_ITEM)], //waroed stem
                [ItemFactory::getInstance()->get(CustomIds::WARPED_PLANKS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::WARPED_STRIPPED_STEM_ITEM)], //warped stripped stem
                [ItemFactory::getInstance()->get(CustomIds::WARPED_PLANKS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::CRIMSON_STRIPPED_STEM_ITEM)], //stripped crimson stem
                [ItemFactory::getInstance()->get(CustomIds::CRIMSON_PLANKS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'BAB',
                    'BAB'
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::STICK), 'B' => ItemFactory::getInstance()->get(CustomIds::WARPED_PLANKS_ITEM)], //warped fence
                [ItemFactory::getInstance()->get(CustomIds::WARPED_FENCE_ITEM, 0, 3)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'BAB',
                    'BAB'
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::STICK), 'B' => ItemFactory::getInstance()->get(CustomIds::CRIMSON_PLANKS_ITEM)], //crimson fence
                [ItemFactory::getInstance()->get(CustomIds::CRIMSON_FENCE_ITEM, 0, 3)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'ABA',
                    'ABA'
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::STICK), 'B' => ItemFactory::getInstance()->get(CustomIds::WARPED_PLANKS_ITEM)], //warped fencegate
                [ItemFactory::getInstance()->get(CustomIds::WARPED_FENCE_GATE_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'ABA',
                    'ABA'
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::STICK), 'B' => ItemFactory::getInstance()->get(CustomIds::CRIMSON_PLANKS_ITEM)], //crimson fencegate
                [ItemFactory::getInstance()->get(CustomIds::CRIMSON_FENCE_GATE_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'BBB',
                    'BBB',
                    ' A '
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::STICK), 'B' => ItemFactory::getInstance()->get(CustomIds::WARPED_PLANKS_ITEM)], //warped sign
                [ItemFactory::getInstance()->get(CustomIds::WARPED_SIGN_ITEM, 0, 3)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'BBB',
                    'BBB',
                    ' A '
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::STICK), 'B' => ItemFactory::getInstance()->get(CustomIds::CRIMSON_PLANKS_ITEM)], //crimson sign
                [ItemFactory::getInstance()->get(CustomIds::CRIMSON_SIGN_ITEM, 0, 3)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
            [
                'AA ',
                'AA ',
                'AA '
            ],
            ['A' => ItemFactory::getInstance()->get(CustomIds::WARPED_PLANKS_ITEM)],
            [ItemFactory::getInstance()->get(CustomIds::WARPED_DOOR_ITEM, 0, 3)]) //warped door
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'AA ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::CRIMSON_PLANKS_ITEM)], //crimson door
                [ItemFactory::getInstance()->get(CustomIds::CRIMSON_DOOR_ITEM, 0, 3)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
            [
                '   ',
                'AAA',
                'AAA'
            ],
            ['A' => ItemFactory::getInstance()->get(CustomIds::WARPED_PLANKS_ITEM)],
            [ItemFactory::getInstance()->get(CustomIds::WARPED_TRAPDOOR_ITEM, 0, 2)]) //warped trapdoor
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::CRIMSON_PLANKS_ITEM)], //crimson trapdoor
                [ItemFactory::getInstance()->get(CustomIds::CRIMSON_TRAPDOOR_ITEM, 0, 2)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
            [
                'A  ',
                'AA ',
                'AAA'
            ],
            ['A' => ItemFactory::getInstance()->get(CustomIds::WARPED_PLANKS_ITEM)],
            [ItemFactory::getInstance()->get(CustomIds::WARPED_STAIRS_ITEM, 0, 4)]) //warped plank stair
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::CRIMSON_PLANKS_ITEM)], //crimson plank stair
                [ItemFactory::getInstance()->get(CustomIds::CRIMSON_STAIRS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
            [
                '   ',
                '   ',
                'AAA'
            ],
            ['A' => ItemFactory::getInstance()->get(CustomIds::WARPED_PLANKS_ITEM)],
            [ItemFactory::getInstance()->get(CustomIds::WARPED_SLAB_ITEM, 0, 6)]) //warped plank slab
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::CRIMSON_PLANKS_ITEM)], //crimson plank slab
                [ItemFactory::getInstance()->get(CustomIds::CRIMSON_SLAB_ITEM, 0, 6)])
        );

        //Netheritems and More End

        //Blackstone Start
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::BLACKSTONE_SLAB_ITEM, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::BLACKSTONE_STAIRS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::BLACKSTONE_WALL_ITEM, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'AAA',
                    'A A',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(ItemIds::FURNACE)])
        );
        //Polished Blackstone end

        //Polished Blackstone Start
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_BUTTON_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'AA ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_PRESSURE_PLATE_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_BRICKS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_SLAB_ITEM, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_STAIRS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_WALL_ITEM, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::BLACKSTONE_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    ' A ',
                    ' A '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_BLACKSTONE_SLAB_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::CHISELED_POLISHED_BLACKSTONE_ITEM)])
        );
        //Polished Blackstone END
        //Copper Ingot Start
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Copper Ingots #wye??
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COPPER_BLOCK_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::COPPER_INGOT_ITEM, 0, 9)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Block of Copper
                [
                    'AAA',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COPPER_INGOT_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::COPPER_BLOCK_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Lightning Rod
                [
                    ' A ',
                    ' A ',
                    ' A '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COPPER_INGOT_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::LIGHNING_ROD_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Spyglass
                [
                    ' A ',
                    ' B ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::AMETHYST_SHARD), 'B' => ItemFactory::getInstance()->get(CustomIds::COPPER_INGOT_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::SPYGLASS)])
        );
        //Copper Ingot End
        //Amethyst
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Getöntes Glas
                [
                    ' A ',
                    'ABA',
                    ' A '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::AMETHYST_SHARD), 'B' => ItemFactory::getInstance()->get(ItemIds::GLASS)],
                [ItemFactory::getInstance()->get(CustomIds::TINTED_GLASS, 0 ,2)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Getöntes Glas
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::AMETHYST_SHARD)],
                [ItemFactory::getInstance()->get(CustomIds::AMETHYST_BLOCK)])
        );

        //Copper Block Start
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Cut Copper
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COPPER_BLOCK_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::CUT_COPPER_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Exposed Cut Copper
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::EXPOSED_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::EXPOSED_CUT_COPPER_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Weathered Cut Copper
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::WEATHERED_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::WEATHERED_CUT_COPPER_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Oxidized Cut Copper
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::OXIDIZED_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::OXIDIZED_CUT_COPPER_ITEM, 0, 4)])
        );

        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Cut Copper Slab
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::CUT_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::CUT_COPPER_SLAB_ITEM, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Exposed Cut Copper Slab
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::EXPOSED_CUT_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::EXPOSED_CUT_COPPER_SLAB_ITEM, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Weathered Cut Copper Slab
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::WEATHERED_CUT_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::WEATHERED_CUT_COPPER_SLAB_ITEM, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Oxidized Cut Copper Slab
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::OXIDIZED_CUT_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::OXIDIZED_CUT_COPPER_SLAB_ITEM, 0, 6)])
        );

        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Cut Copper Stairs
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::CUT_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::CUT_COPPER_STAIRS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Exposed Cut Copper Stairs
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::EXPOSED_CUT_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::EXPOSED_CUT_COPPER_STAIRS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Weathered Cut Copper Stairs
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::WEATHERED_CUT_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::WEATHERED_CUT_COPPER_STAIRS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Oxidized Cut Copper Stairs
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::OXIDIZED_CUT_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::OXIDIZED_CUT_COPPER_STAIRS_ITEM, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #waxed copperblock
                [
                    '   ',
                    'AB ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COPPER_BLOCK_ITEM), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::WAXED_COPPER_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #waxed exposed copper block
                [
                    '   ',
                    'AB ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::EXPOSED_COPPER_ITEM), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::WAXED_EXPOSED_COPPER_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #waxed weathered copper block
                [
                    '   ',
                    'AB ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::WEATHERED_COPPER_ITEM), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::WAXED_WEATHERED_COPPER_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #waxed oxidized copper block
                [
                    '   ',
                    'AB ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::OXIDIZED_COPPER_ITEM), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::WAXED_OXIDIZED_COPPER_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #waxed cut copperblock
                [
                    '   ',
                    'AB ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::CUT_COPPER_ITEM), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::WAXED_CUT_COPPER_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #waxed cut exposed copper block
                [
                    '   ',
                    'AB ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::EXPOSED_CUT_COPPER_ITEM), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::WAXED_EXPOSED_CUT_COPPER_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #waxed cut weathered copper block
                [
                    '   ',
                    'AB ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::WEATHERED_CUT_COPPER_ITEM), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::WAXED_WEATHERED_CUT_COPPER_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #waxed cut oxidized copper block
                [
                    '   ',
                    'AB ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::OXIDIZED_CUT_COPPER_ITEM), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::WAXED_OXIDIZED_CUT_COPPER_ITEM)])
        );

        //Other
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Raw Copper
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_BLOCK_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_ITEM, 0, 9)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Raw Copper Block
                [
                    'AAA',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_BLOCK_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Raw Copper
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_BLOCK_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_ITEM, 0, 9)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Raw Copper Block
                [
                    'AAA',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::RAW_COPPER_BLOCK_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Raw Iron
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::RAW_IRON_BLOCK_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::RAW_IRON_ITEM, 0, 9)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Raw Iron Block
                [
                    'AAA',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::RAW_IRON_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::RAW_IRON_BLOCK_ITEM)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Raw Gold
                [
                    '   ',
                    ' A ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::RAW_GOLD_BLOCK_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::RAW_GOLD_ITEM, 0, 9)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #Raw Gold Block
                [
                    'AAA',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::RAW_GOLD_ITEM)],
                [ItemFactory::getInstance()->get(CustomIds::RAW_GOLD_BLOCK_ITEM)])
        );


        //Netherite
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    ' A ',
                    ' A ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT), 'B' => ItemFactory::getInstance()->get(ItemIds::STICK)],
                [ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_SWORD)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    ' A ',
                    ' B ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT), 'B' => VanillaItems::STICK()],
                [ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_SHOVEL)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'AAA',
                    ' B ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT), 'B' => VanillaItems::STICK()],
                [ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_PICKAXE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    ' AA',
                    ' BA',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT), 'B' => VanillaItems::STICK()],
                [ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_AXE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'AA ',
                    'AB ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT), 'B' => VanillaItems::STICK()],
                [ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_AXE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'AA ',
                    ' B ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT), 'B' => VanillaItems::STICK()],
                [ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_HOE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    ' AA',
                    ' B ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT), 'B' => VanillaItems::STICK()],
                [ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_HOE)])
        );

        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'AAA',
                    'A A',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT)],
                [ItemFactory::getInstance()->get(CustomIds::NETHERITE_HELMET)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'A A',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT)],
                [ItemFactory::getInstance()->get(CustomIds::NETHERITE_CHESTPLATE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'AAA',
                    'A A',
                    'A A'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT)],
                [ItemFactory::getInstance()->get(CustomIds::NETHERITE_LEGGINGS)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    '   ',
                    'A A',
                    'A A'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT)],
                [ItemFactory::getInstance()->get(CustomIds::NETHERITE_BOOTS)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
                [
                    'A A',
                    'A A',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::ITEM_NETHERITE_INGOT)],
                [ItemFactory::getInstance()->get(CustomIds::NETHERITE_BOOTS)])
        );
        //Cobbled Deepslate
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //Furnace
                [
                    'AAA',
                    'A A',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE)],
                [ItemFactory::getInstance()->get(ItemIds::FURNACE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //Brewingstand
                [
                    '   ',
                    ' B ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE), 'B' => ItemFactory::getInstance()->get(ItemIds::BLAZE_ROD)],
                [ItemFactory::getInstance()->get(ItemIds::BREWING_STAND)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //sword
                [
                    ' A ',
                    ' A ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE), 'B' => ItemFactory::getInstance()->get(ItemIds::STICK)],
                [ItemFactory::getInstance()->get(ItemIds::STONE_SWORD)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //shovel
                [
                    ' A ',
                    ' B ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE), 'B' => ItemFactory::getInstance()->get(ItemIds::STICK)],
                [ItemFactory::getInstance()->get(ItemIds::STONE_SHOVEL)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //axe
                [
                    'AA ',
                    'AB ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE), 'B' => ItemFactory::getInstance()->get(ItemIds::STICK)],
                [ItemFactory::getInstance()->get(ItemIds::STONE_AXE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //pickaxe
                [
                    'AAA',
                    ' B ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE), 'B' => ItemFactory::getInstance()->get(ItemIds::STICK)],
                [ItemFactory::getInstance()->get(ItemIds::STONE_PICKAXE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //hoe
                [
                    'AA ',
                    ' B ',
                    ' B '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE), 'B' => ItemFactory::getInstance()->get(ItemIds::STICK)],
                [ItemFactory::getInstance()->get(ItemIds::STONE_HOE)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //slab
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE)],
                [ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE_SLAB, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //stairs
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE)],
                [ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE_STAIRS, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //wall
                [
                    '   ',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE)],
                [ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE_WALL, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //polished deepslate
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::COBBLED_DEEPSLATE)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_DEEPSLATE, 0, 4)])
        );
        //Polished Deepslate
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //slab
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_DEEPSLATE)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_DEEPSLATE_SLAB, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //stairs
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_DEEPSLATE)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_DEEPSLATE_STAIRS, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //wall
                [
                    '   ',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_DEEPSLATE)],
                [ItemFactory::getInstance()->get(CustomIds::POLISHED_DEEPSLATE_WALL, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //deepslate bricks
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::POLISHED_DEEPSLATE)],
                [ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICKS, 0, 4)])
        );
        //Deepslate Bricks
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //slab
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICKS)],
                [ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICK_SLAB, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //stairs
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICKS)],
                [ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICK_STAIRS, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //wall
                [
                    '   ',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICKS)],
                [ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICK_WALL, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //deepslate tiles
                [
                    '   ',
                    'AA ',
                    'AA '
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_BRICKS)],
                [ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_TILES, 0, 4)])
        );
        //Deepslate Tiles
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //slab
                [
                    '   ',
                    '   ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_TILES)],
                [ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_TILE_SLAB, 0, 6)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //stairs
                [
                    'A  ',
                    'AA ',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_TILES)],
                [ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_TILE_STAIRS, 0, 4)])
        );
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( //wall
                [
                    '   ',
                    'AAA',
                    'AAA'
                ],
                ['A' => ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_TILES)],
                [ItemFactory::getInstance()->get(CustomIds::DEEPSLATE_TILE_WALL, 0, 6)])
        );
        //Candle
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe( #candle
                [
                    ' A ',
                    ' B ',
                    '   '
                ],
                ['A' => ItemFactory::getInstance()->get(ItemIds::STRING), 'B' => ItemFactory::getInstance()->get(CustomIds::HONEYCOMB)],
                [ItemFactory::getInstance()->get(CustomIds::CANDLE)])
        );
    }
    public static function playSound(Player|Vector3 $player, string $sound, float $pitch = 1, float $volume = 1, bool $packet = false): ?DataPacket{
        $pos = $player instanceof Player ? $player->getPosition() : $player;
        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $pos->x;
        $pk->y = $pos->y;
        $pk->z = $pos->z;
        $pk->pitch = $pitch;
        $pk->volume = $volume;
        if($packet){
            return $pk;
        }elseif($player instanceof Player){
            $player->getNetworkSession()->sendDataPacket($pk);
        }
        return null;
    }
}