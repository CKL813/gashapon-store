<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    // ── Catalogue Data ────────────────────────────────────────────────────────

    private array $brands = [
        ['name' => 'Bandai Gashapon',        'description' => 'Official Bandai capsule toy division, home of iconic anime figures.'],
        ['name' => 'Good Smile Company',     'description' => 'Premium figure maker famous for Nendoroid and Figma lines.'],
        ['name' => 'Takara Tomy A.R.T.S',    'description' => 'Creators of highly detailed mini-figure collections and blind boxes.'],
        ['name' => 'Kitan Club',             'description' => 'Specialists in adorable animal capsule figures with realistic detail.'],
        ['name' => 'Re-Ment Co.',            'description' => 'Masters of food and lifestyle miniature sets loved worldwide.'],
        ['name' => 'Kenelephant',            'description' => 'Creative capsule brand known for quirky and cute character designs.'],
        ['name' => 'Epoch Co.',              'description' => 'Makers of Sylvanian Families and quality collectible figure series.'],
        ['name' => 'Yell Co. Ltd',           'description' => 'Independent capsule brand specialising in animal and nature series.'],
    ];

    private array $categories = [
        ['name' => 'Action Figures',    'description' => 'Heroic characters from your favourite anime and game franchises.', 'sort_order' => 1],
        ['name' => 'Anime & Manga',     'description' => 'Collectible figures from the best anime and manga series.', 'sort_order' => 2],
        ['name' => 'Cute Animals',      'description' => 'Adorable mini animals in charming poses and situations.', 'sort_order' => 3],
        ['name' => 'Food Miniatures',   'description' => 'Incredibly detailed miniature food replicas for collectors.', 'sort_order' => 4],
        ['name' => 'Robots & Mechas',   'description' => 'Mecha suits and robots from classic and modern series.', 'sort_order' => 5],
        ['name' => 'Fantasy & Magic',   'description' => 'Mystical creatures, wizards, and enchanted worlds.', 'sort_order' => 6],
        ['name' => 'Retro & Vintage',   'description' => 'Nostalgia-inducing collectibles from gaming and pop culture history.', 'sort_order' => 7],
        ['name' => 'Sci-Fi & Space',    'description' => 'Spaceships, aliens, and futuristic figures from across the galaxy.', 'sort_order' => 8],
        ['name' => 'Kawaii Lifestyle',  'description' => 'Sanrio and kawaii characters living their cutest daily lives.', 'sort_order' => 9],
        ['name' => 'Limited Edition',   'description' => 'Seasonal specials and exclusive releases — grab them before they\'re gone!', 'sort_order' => 10],
    ];

    /**
     * Products grouped by category.
     * Each entry: [name, type, price HKD, wholesale_price, stock, is_featured, variants[]]
     * Variants only for 'specific' type products. Each variant: [name, stock, price_override|null]
     */
    private array $products = [

        'Action Figures' => [
            ['Dragon Ball Z Son Goku Super Saiyan Vol.3', 'specific', 89.00,  62.00, 120, true,
                ['Base Form', 'Super Saiyan', 'Super Saiyan Blue', 'Ultra Instinct']],
            ['Naruto Shippuden Chibi Collection', 'random', 45.00, 31.50, 200, false, []],
            ['One Piece Straw Hat Crew Mini Set', 'specific', 129.00, 90.30, 80, true,
                ['Monkey D. Luffy', 'Roronoa Zoro', 'Nami', 'Usopp', 'Sanji']],
            ['Attack on Titan Survey Corps Vol.2', 'specific', 79.00, 55.30, 95, false,
                ['Eren Yeager', 'Mikasa Ackerman', 'Levi Ackerman', 'Armin Arlert']],
            ['Demon Slayer Hashira Collection', 'random', 55.00, 38.50, 180, false, []],
            ['My Hero Academia Pro Heroes Set', 'specific', 99.00, 69.30, 110, false,
                ['All Might', 'Endeavor', 'Hawks', 'Eraser Head']],
            ['Jujutsu Kaisen Sorcerers Vol.1', 'random', 49.00, 34.30, 220, true, []],
            ['Fullmetal Alchemist Brothers Figure', 'specific', 75.00, 52.50, 60, false,
                ['Edward Elric', 'Alphonse Elric', 'Roy Mustang', 'Riza Hawkeye']],
            ['Sword Art Online Characters Series', 'specific', 85.00, 59.50, 70, false,
                ['Kirito', 'Asuna', 'Sinon', 'Leafa']],
            ['Hunter x Hunter Trading Figures', 'random', 52.00, 36.40, 150, false, []],
        ],

        'Anime & Manga' => [
            ['Spy x Family Forger Family Set', 'specific', 119.00, 83.30, 90, true,
                ['Loid Forger', 'Yor Forger', 'Anya Forger', 'Bond the Dog']],
            ['Chainsaw Man Devil Collection', 'random', 58.00, 40.60, 160, false, []],
            ['Vinland Saga Warriors Series', 'specific', 95.00, 66.50, 55, false,
                ['Thorfinn', 'Askeladd', 'Canute', 'Bjorn']],
            ['Tokyo Revengers Toman Gang Vol.1', 'specific', 78.00, 54.60, 100, false,
                ['Takemichi', 'Mikey', 'Draken', 'Baji']],
            ['Bleach Thousand-Year Blood War Set', 'random', 62.00, 43.40, 140, true, []],
            ['Fairy Tail Guild Members Collection', 'random', 48.00, 33.60, 190, false, []],
            ['Re:Zero Season 2 Figure Set', 'specific', 108.00, 75.60, 75, false,
                ['Subaru Natsuki', 'Emilia', 'Rem', 'Ram', 'Beatrice']],
            ['Overlord Dark Hero Mini Figures', 'specific', 88.00, 61.60, 85, false,
                ['Ainz Ooal Gown', 'Albedo', 'Demiurge', 'Shalltear']],
            ['Black Clover Magic Knights Series', 'random', 45.00, 31.50, 200, false, []],
            ['Konosuba Adventurers Party Set', 'specific', 99.00, 69.30, 65, false,
                ['Kazuma', 'Aqua', 'Megumin', 'Darkness']],
        ],

        'Cute Animals' => [
            ['Shiba Inu Expressions Vol.4', 'random', 39.00, 27.30, 250, true, []],
            ['Capybara Relaxing Situations', 'random', 42.00, 29.40, 230, false, []],
            ['Corgi Butts Wiggle Collection', 'random', 35.00, 24.50, 280, true, []],
            ['Hamster in Tiny Places', 'random', 38.00, 26.60, 260, false, []],
            ['Cat Life Moments Vol.6', 'random', 36.00, 25.20, 300, false, []],
            ['Fluffy Rabbit Friends Series', 'random', 40.00, 28.00, 240, false, []],
            ['Penguin Waddle Parade', 'random', 37.00, 25.90, 270, false, []],
            ['Frog on Objects Collection', 'random', 35.00, 24.50, 290, false, []],
            ['Baby Alpaca Nap Time', 'random', 44.00, 30.80, 210, false, []],
            ['Otter Floating Family Set', 'random', 41.00, 28.70, 225, false, []],
        ],

        'Food Miniatures' => [
            ['HK Dim Sum Feast Collection', 'random', 55.00, 38.50, 180, true, []],
            ['Japanese Sushi Counter Set', 'specific', 148.00, 103.60, 45, true,
                ['Nigiri Tray A', 'Nigiri Tray B', 'Maki Roll Set', 'Chef\'s Special']],
            ['Bubble Tea Flavours Series', 'random', 42.00, 29.40, 220, false, []],
            ['Ramen Bowl Detail Collection', 'random', 48.00, 33.60, 195, false, []],
            ['HK Street Food Miniatures', 'random', 45.00, 31.50, 210, false, []],
            ['Matcha Sweets Parfait Set', 'random', 40.00, 28.00, 240, false, []],
            ['Takoyaki Street Stand Mini', 'specific', 88.00, 61.60, 60, false,
                ['Classic Sauce', 'Cheese & Mentaiko', 'Kimchi', 'Wasabi Mayo']],
            ['Ice Cream World Flavours', 'random', 38.00, 26.60, 260, false, []],
            ['Japanese Convenience Store Snacks', 'random', 44.00, 30.80, 200, false, []],
            ['Afternoon Tea Set Miniatures', 'specific', 118.00, 82.60, 50, false,
                ['English Classic', 'French Patisserie', 'Japanese Wagashi', 'HK Style']],
        ],

        'Robots & Mechas' => [
            ['Gundam RX-78-2 Origin Ver.', 'specific', 159.00, 111.30, 40, true,
                ['White Base Colours', 'G-Fighter Colours', 'Zeon Captured', 'Last Shooting']],
            ['Evangelion Unit-01 Awakening Mode', 'specific', 145.00, 101.50, 35, true,
                ['Standard Mode', 'Beast Mode', 'Angel Absorbed', 'Berserk Mode']],
            ['Voltron Legendary Defender Set', 'specific', 129.00, 90.30, 45, false,
                ['Black Lion', 'Red Lion', 'Blue Lion', 'Green Lion', 'Yellow Lion']],
            ['Gurren Lagann Final Battle', 'specific', 138.00, 96.60, 38, false,
                ['Gurren Lagann', 'Arc-Gurren Lagann', 'Super Galaxy', 'Tengen Toppa']],
            ['Code Geass Knightmare Frames', 'specific', 115.00, 80.50, 55, false,
                ['Lancelot', 'Guren Mk-II', 'Tristan', 'Mordred']],
            ['Macross Valkyrie Fighter Set', 'specific', 135.00, 94.50, 42, false,
                ['VF-1S Roy Focker', 'VF-1J Hikaru', 'VF-1A Max', 'VF-19 Fire Valkyrie']],
            ['Mazinger Z Classic Villain Pack', 'random', 68.00, 47.60, 120, false, []],
            ['Pacific Rim Jaeger Collection', 'specific', 125.00, 87.50, 48, false,
                ['Gipsy Danger', 'Striker Eureka', 'Cherno Alpha', 'Crimson Typhoon']],
            ['Getter Robo Armageddon Vol.2', 'specific', 112.00, 78.40, 50, false,
                ['Getter-1', 'Getter-2', 'Getter-3', 'Getter Emperor']],
            ['Iron Man Hall of Armour Mini', 'random', 72.00, 50.40, 130, false, []],
        ],

        'Fantasy & Magic' => [
            ['Princess Mononoke Forest Spirits', 'random', 62.00, 43.40, 140, true, []],
            ['Spirited Away Spirit Collection', 'random', 58.00, 40.60, 155, true, []],
            ['Howl\'s Moving Castle Mini Set', 'specific', 135.00, 94.50, 40, false,
                ['The Castle', 'Sophie', 'Howl in Black', 'Howl in Blue', 'Calcifer']],
            ['Dragon & Knight Battle Series', 'specific', 108.00, 75.60, 55, false,
                ['Red Dragon vs. Paladin', 'Ice Dragon vs. Ranger', 'Gold Dragon Guardian']],
            ['Witch\'s Potion Bottles Series', 'random', 45.00, 31.50, 200, false, []],
            ['Mythical Creatures Blind Box', 'random', 52.00, 36.40, 170, false, []],
            ['Dungeons & Dragons Monster Box', 'random', 65.00, 45.50, 135, false, []],
            ['Enchanted Forest Beings', 'random', 48.00, 33.60, 185, false, []],
            ['Harry Potter House Mascots', 'specific', 95.00, 66.50, 70, false,
                ['Gryffindor Lion', 'Slytherin Serpent', 'Hufflepuff Badger', 'Ravenclaw Eagle']],
            ['Lord of the Rings Fellowship', 'specific', 125.00, 87.50, 45, false,
                ['Frodo Baggins', 'Gandalf the Grey', 'Aragorn', 'Legolas', 'Gimli']],
        ],

        'Retro & Vintage' => [
            ['Super Mario Bros. Classic Series', 'specific', 89.00, 62.30, 100, true,
                ['Mario', 'Luigi', 'Princess Peach', 'Toad', 'Yoshi', 'Bowser']],
            ['Pac-Man Arcade Heroes Set', 'specific', 75.00, 52.50, 80, false,
                ['Pac-Man', 'Blinky', 'Pinky', 'Inky', 'Clyde']],
            ['Game Boy Era Handheld Collection', 'specific', 115.00, 80.50, 50, false,
                ['Original Gray', 'Game Boy Pocket', 'Game Boy Color', 'Game Boy Advance']],
            ['80s Pop Culture Nostalgia Box', 'random', 55.00, 38.50, 160, true, []],
            ['Classic Tin Toy Vehicles', 'specific', 98.00, 68.60, 65, false,
                ['Red Fire Truck', 'Blue Police Car', 'Yellow Taxi', 'Green Army Jeep']],
            ['Retro Robot Wind-Up Series', 'random', 62.00, 43.40, 130, false, []],
            ['Old Hong Kong Street Scenes', 'specific', 138.00, 96.60, 35, false,
                ['Cha Chaan Teng', 'Tram on Des Voeux Rd', 'Star Ferry Pier', 'Temple St Night']],
            ['Vintage Space Age Collectibles', 'random', 58.00, 40.60, 145, false, []],
            ['Cassette Tape Art Series', 'specific', 85.00, 59.50, 75, false,
                ['80s Rock Mix', 'City Pop Hits', 'Hip Hop Classics', 'J-Pop Gold']],
            ['Classic Pinball Machine Minis', 'random', 65.00, 45.50, 120, false, []],
        ],

        'Sci-Fi & Space' => [
            ['Alien Xenomorph Life Cycle Set', 'specific', 125.00, 87.50, 50, true,
                ['Facehugger', 'Chestburster', 'Warrior', 'Queen', 'Dog Alien']],
            ['Predator Trophy Hunter Series', 'specific', 115.00, 80.50, 55, false,
                ['Classic Predator', 'City Hunter', 'Berserker', 'Elder Predator']],
            ['2001: A Space Odyssey Monolith', 'specific', 95.00, 66.50, 60, false,
                ['Monolith Black', 'HAL 9000 Eye', 'Discovery One', 'Moon Bus']],
            ['Mars Rover Exploration Set', 'specific', 108.00, 75.60, 45, false,
                ['Sojourner 1997', 'Spirit 2004', 'Opportunity', 'Curiosity', 'Perseverance']],
            ['Cosmic Horror Figures Vol.1', 'random', 68.00, 47.60, 125, true, []],
            ['Starship Collection Series', 'random', 72.00, 50.40, 115, false, []],
            ['Space Station Module Set', 'specific', 138.00, 96.60, 38, false,
                ['ISS Module A', 'ISS Module B', 'Crew Capsule', 'Space Telescope']],
            ['Alien Worlds Creature Box', 'random', 58.00, 40.60, 150, false, []],
            ['Astronaut Life Moments', 'specific', 118.00, 82.60, 48, false,
                ['EVA Walk', 'Lunar Landing', 'Zero-G Meal', 'ISS Workout']],
            ['Deep Sea vs. Space Explorer', 'random', 55.00, 38.50, 165, false, []],
        ],

        'Kawaii Lifestyle' => [
            ['Cinnamoroll Café Moments Set', 'specific', 98.00, 68.60, 85, true,
                ['Coffee Art', 'Cake Slice', 'Morning Toast', 'Milk Glass', 'Chiffon Cake']],
            ['Hello Kitty Through the Years', 'random', 65.00, 45.50, 140, true, []],
            ['Pompompurin Nap Time Series', 'random', 45.00, 31.50, 210, false, []],
            ['My Melody Kitchen Life', 'random', 42.00, 29.40, 225, false, []],
            ['Keroppi Pond Adventures', 'random', 40.00, 28.00, 240, false, []],
            ['Gudetama Existential Crisis', 'random', 38.00, 26.60, 260, false, []],
            ['Aggretsuko Office Survival', 'specific', 88.00, 61.60, 70, false,
                ['Death Metal Mode', 'Presentation Panic', 'Retsuko Rage', 'Fenneko Observe']],
            ['Pochacco Sports Champions', 'random', 42.00, 29.40, 220, false, []],
            ['Badtz-Maru Rock Star Life', 'specific', 78.00, 54.60, 80, false,
                ['Guitar Hero', 'Drum Kit', 'Bass Player', 'Lead Vocalist']],
            ['Little Twin Stars Magic Night', 'specific', 95.00, 66.50, 65, false,
                ['Kiki Blue', 'Lala Pink', 'Star Cart Duo', 'Cloud Bed Set']],
        ],

        'Limited Edition' => [
            ['Year of the Snake 2025 Special', 'specific', 168.00, 117.60, 30, true,
                ['Gold Fortune', 'Jade Prosperity', 'Ruby Blessing', 'Pearl Wisdom']],
            ['Christmas Winter Wonderland Box', 'random', 78.00, 54.60, 100, true, []],
            ['Halloween Spooky Surprise Set', 'random', 72.00, 50.40, 110, false, []],
            ['Valentine\'s Love Collection 2025', 'specific', 138.00, 96.60, 40, false,
                ['Cupid\'s Arrow', 'Love Letter', 'Chocolate Heart', 'Rose Bouquet']],
            ['Cherry Blossom Hanami Season', 'random', 65.00, 45.50, 120, false, []],
            ['Hong Kong 30th Anniversary Set', 'specific', 188.00, 131.60, 25, true,
                ['Victoria Harbour', 'Neon Signs', 'Star Ferry', 'Lion Rock', 'Big Buddha']],
            ['Mid-Autumn Festival Moon Box', 'random', 88.00, 61.60, 90, false, []],
            ['Lunar New Year Gold Fortune', 'specific', 158.00, 110.60, 35, false,
                ['Money Cat', 'Lucky Koi Fish', 'Plum Blossom', 'Red Lanterns']],
            ['Summer Matsuri Festival Series', 'random', 62.00, 43.40, 115, false, []],
            ['Winter Sonata Snow Collection', 'specific', 128.00, 89.60, 42, false,
                ['Snowflake A', 'Snowflake B', 'Hot Cocoa', 'Kotatsu Cat']],
        ],
    ];

    // ── Run ───────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->command->info('Seeding brands…');
        $brands = $this->seedBrands();

        $this->command->info('Seeding categories…');
        $categories = $this->seedCategories();

        $this->command->info('Seeding 100 products…');
        $count = $this->seedProducts($brands, $categories);

        $this->command->info("✓ Done — {$count} products created.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function seedBrands(): array
    {
        $brandModels = [];
        foreach ($this->brands as $data) {
            $brandModels[] = Brand::firstOrCreate(
                ['name' => $data['name']],
                [
                    'slug'        => Str::slug($data['name']),
                    'description' => $data['description'],
                    'is_active'   => true,
                ],
            );
        }
        return $brandModels;
    }

    private function seedCategories(): array
    {
        $catModels = [];
        foreach ($this->categories as $data) {
            $catModels[$data['name']] = Category::firstOrCreate(
                ['name' => $data['name']],
                [
                    'slug'        => Str::slug($data['name']),
                    'description' => $data['description'],
                    'sort_order'  => $data['sort_order'],
                    'is_active'   => true,
                ],
            );
        }
        return $catModels;
    }

    private function seedProducts(array $brands, array $categories): int
    {
        $count         = 0;
        $brandCount    = count($brands);
        $descriptions  = $this->descriptions();

        foreach ($this->products as $categoryName => $productList) {
            $category = $categories[$categoryName] ?? null;
            if (! $category) {
                continue;
            }

            foreach ($productList as [$name, $type, $price, $wholesale, $stock, $featured, $variants]) {
                $brand = $brands[($count % $brandCount)];
                $slug  = Str::slug($name);

                // Avoid slug collisions if seeded twice
                $existingSlug = Product::where('slug', $slug)->exists();
                if ($existingSlug) {
                    $slug .= '-' . strtolower(Str::random(4));
                }

                $product = Product::create([
                    'brand_id'        => $brand->id,
                    'category_id'     => $category->id,
                    'sku'             => strtoupper(Str::random(8)),
                    'name'            => $name,
                    'slug'            => $slug,
                    'description'     => $descriptions[array_rand($descriptions)],
                    'product_type'    => $type === 'random' ? ProductType::Random : ProductType::Specific,
                    'price'           => $price,
                    'wholesale_price' => $wholesale,
                    'stock'           => $stock,
                    'is_active'       => true,
                    'is_featured'     => $featured,
                ]);

                // Create variants for specific-type products
                if ($type === 'specific' && ! empty($variants)) {
                    foreach (array_values($variants) as $i => $variantName) {
                        ProductVariant::create([
                            'product_id'      => $product->id,
                            'sku'             => strtoupper(Str::random(10)),
                            'name'            => $variantName,
                            'price'           => null, // inherits product price
                            'wholesale_price' => null,
                            'stock'           => (int) ceil($stock / count($variants)),
                            'sort_order'      => $i,
                            'is_active'       => true,
                        ]);
                    }
                }

                $count++;
            }
        }

        return $count;
    }

    /** A pool of realistic gashapon product descriptions to rotate through. */
    private function descriptions(): array
    {
        return [
            'Highly detailed miniature figure crafted from premium ABS plastic with hand-painted finish. Each piece measures approximately 5–7cm and comes in a collectible capsule.',
            'Part of our popular blind-box capsule series — collect all variants! Features intricate detail work and vibrant colours. Perfect for display or gifting.',
            'Limited run collector\'s item with metallic accent paint. Sculpted by a master artist and produced with Bandai\'s signature quality. Stand included.',
            'An adorable addition to any collection. This charming figure captures every little detail with precision sculpting and vivid colouring. Display-ready.',
            'From the fan-favourite capsule series, this figure features articulated joints and interchangeable accessories. Comes with a display stand.',
            'Super-deformed chibi style with maximum cuteness! A compact 4cm figure full of character, perfect for desk decoration or bag charm.',
            'Faithfully recreated miniature with authentic colours and markings. Part of a numbered collection series — spot them all!',
            'One of the most sought-after entries in this capsule series. Ultra-fine detail, premium paint, and a unique pose make this a must-have for collectors.',
            'Surprise yourself! Each capsule contains one of the hidden variants in this series. Can you collect the whole set? Trading welcome.',
            'A collaboration between top sculptors and the original IP rights holders ensures 100% accuracy to the source material. Museum-quality in your palm.',
            'Crafted with food-grade ABS plastic and non-toxic paint. Safe for display on your desk, shelf, or in a display case. UV-resistant coating included.',
            'This limited seasonal release features exclusive colourways not available in the standard series. Only 1,000 units produced worldwide.',
            'The perfect gift for any collector! Comes in a premium gift box with a certificate of authenticity and storage compartment.',
            'Showcasing incredible attention to detail at 1:12 scale, this miniature brings a burst of character to any display space.',
            'Each figure in this series tells a story. Combine multiple pieces to create stunning diorama-style displays on your shelf.',
        ];
    }
}
