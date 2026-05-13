<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@raktrek.test'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password'),
                'role'     => 'staff',
                'phone'    => '081234567890',
                'address'  => 'Jl. Perpustakaan No. 1, Jakarta',
            ]
        );

        $genres = $this->seedGenres();
        $authors = $this->seedAuthors();
        $this->seedBooks($authors, $genres);
    }

    private function seedGenres(): array
    {
        $data = [
            ['name' => 'Fiction',         'description' => 'Narrative works of imagination, not based on real events.'],
            ['name' => 'Non-Fiction',     'description' => 'Works based on real events, people, and facts.'],
            ['name' => 'Mystery',         'description' => 'Stories centered around solving a crime or uncovering secrets.'],
            ['name' => 'Fantasy',         'description' => 'Stories featuring magical elements and fictional worlds.'],
            ['name' => 'Science Fiction', 'description' => 'Speculative fiction exploring futuristic science and technology.'],
            ['name' => 'Romance',         'description' => 'Stories focusing on love and romantic relationships.'],
            ['name' => 'Historical',      'description' => 'Stories set in the past, often with accurate historical detail.'],
            ['name' => 'Biography',       'description' => 'Account of a person\'s life written by someone else.'],
            ['name' => 'Self-Help',       'description' => 'Books intended to guide readers in improving their lives.'],
            ['name' => 'Adventure',       'description' => 'Stories featuring exciting journeys and daring exploits.'],
            ['name' => 'Classic',         'description' => 'Works widely considered to be of lasting literary quality.'],
            ['name' => 'Literary Fiction','description' => 'Character-driven fiction with emphasis on prose style and theme.'],
        ];

        $genres = [];
        foreach ($data as $row) {
            $genres[$row['name']] = Genre::firstOrCreate(['name' => $row['name']], $row);
        }
        return $genres;
    }

    private function seedAuthors(): array
    {
        $data = [
            [
                'name'  => 'J.K. Rowling',
                'about' => 'Joanne Rowling, known by her pen name J.K. Rowling, is a British author best known for writing the Harry Potter fantasy series. The books have won multiple awards and have sold more than 500 million copies worldwide.',
            ],
            [
                'name'  => 'George Orwell',
                'about' => 'Eric Arthur Blair, known by his pen name George Orwell, was an English novelist, essayist, journalist, and critic. His work is characterised by lucid prose, social criticism, opposition to totalitarianism, and support of democratic socialism.',
            ],
            [
                'name'  => 'Harper Lee',
                'about' => 'Nelle Harper Lee was an American novelist best known for her 1960 novel To Kill a Mockingbird. It won the 1961 Pulitzer Prize and has become a classic of modern American literature.',
            ],
            [
                'name'  => 'J.R.R. Tolkien',
                'about' => 'John Ronald Reuel Tolkien was an English writer and philologist. He is best known as the author of the high fantasy works The Hobbit and The Lord of the Rings.',
            ],
            [
                'name'  => 'Agatha Christie',
                'about' => 'Dame Agatha Mary Clarissa Christie was an English writer known for her 66 detective novels and 14 short story collections. She is often referred to as the "Queen of Mystery".',
            ],
            [
                'name'  => 'Jane Austen',
                'about' => 'Jane Austen was an English novelist known primarily for her six major novels which interpret, critique and comment upon the British landed gentry at the end of the 18th century.',
            ],
            [
                'name'  => 'Gabriel García Márquez',
                'about' => 'Gabriel José de la Concordia García Márquez was a Colombian novelist and Nobel Prize laureate. He is considered one of the most significant authors of the 20th century and a master of magical realism.',
            ],
            [
                'name'  => 'Pramoedya Ananta Toer',
                'about' => 'Pramoedya Ananta Toer was an Indonesian author of novels, short stories, essays, and other works. He was one of Asia\'s most prominent authors and has been called the greatest Indonesian author of the 20th century.',
            ],
            [
                'name'  => 'Andrea Hirata',
                'about' => 'Andrea Hirata is an Indonesian author best known for his debut novel Laskar Pelangi (Rainbow Troops), which became the best-selling novel in Indonesian history and was adapted into a film.',
            ],
            [
                'name'  => 'Tere Liye',
                'about' => 'Tere Liye is the pen name of Darwis, an Indonesian author known for writing numerous bestselling novels across various genres including romance, family, and fantasy.',
            ],
        ];

        $authors = [];
        foreach ($data as $row) {
            $authors[$row['name']] = Author::firstOrCreate(['name' => $row['name']], $row);
        }
        return $authors;
    }

    private function seedBooks(array $authors, array $genres): void
    {
        $books = [
            [
                'title'            => 'Harry Potter and the Philosopher\'s Stone',
                'author'           => 'J.K. Rowling',
                'genres'           => ['Fantasy', 'Fiction', 'Adventure'],
                'page_number'      => 223,
                'synopsis'         => 'Harry Potter has never even heard of Hogwarts when the letters start dropping on the doormat at number four, Privet Drive. Addressed in green ink on yellowish parchment with a purple seal, they are swiftly confiscated by his grisly aunt and uncle. Then, on Harry\'s eleventh birthday, a great beetle-eyed giant of a man called Rubeus Hagrid bursts in with some astonishing news: Harry Potter is a wizard, and he has a place at Hogwarts School of Witchcraft and Wizardry.',
                'publication_year' => 1997,
                'publisher'        => 'Bloomsbury Publishing',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => 'Harry Potter and the Chamber of Secrets',
                'author'           => 'J.K. Rowling',
                'genres'           => ['Fantasy', 'Fiction', 'Adventure'],
                'page_number'      => 251,
                'synopsis'         => 'The Dursleys were so mean and hideous that summer that all Harry Potter wanted was to get back to the Hogwarts School for Witchcraft and Wizardry. But just as he\'s packing his bags, Harry receives a warning from a strange, impish creature named Dobby who says that if Harry Potter returns to Hogwarts, disaster will strike.',
                'publication_year' => 1998,
                'publisher'        => 'Bloomsbury Publishing',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => '1984',
                'author'           => 'George Orwell',
                'genres'           => ['Fiction', 'Science Fiction', 'Classic'],
                'page_number'      => 328,
                'synopsis'         => 'Among the seminal texts of the 20th century, Nineteen Eighty-Four is a rare work that grows more haunting as its futuristic purgatory becomes more real. Published in 1949, the book offers political satirist George Orwell\'s nightmarish vision of a totalitarian, bureaucratic world and one poor stiff\'s attempt to find individuality.',
                'publication_year' => 1949,
                'publisher'        => 'Secker & Warburg',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => 'Animal Farm',
                'author'           => 'George Orwell',
                'genres'           => ['Fiction', 'Classic'],
                'page_number'      => 112,
                'synopsis'         => 'A farm is taken over by its overworked, mistreated animals. With flaming idealism and stirring slogans, they set out to create a paradise of progress, justice, and equality. Thus the stage is set for one of the most telling satiric fables ever penned, a razor-edged fairy tale for grown-ups that records the evolution from revolution against tyranny to a totalitarianism just as terrible.',
                'publication_year' => 1945,
                'publisher'        => 'Secker & Warburg',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => 'To Kill a Mockingbird',
                'author'           => 'Harper Lee',
                'genres'           => ['Fiction', 'Classic', 'Literary Fiction'],
                'page_number'      => 281,
                'synopsis'         => 'The unforgettable novel of a childhood in a sleepy Southern town and the crisis of conscience that rocked it. To Kill a Mockingbird became both an instant bestseller and a critical success when it was first published in 1960. It went on to win the Pulitzer Prize in 1961 and was later made into an Academy Award-winning film.',
                'publication_year' => 1960,
                'publisher'        => 'J. B. Lippincott & Co.',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => 'The Hobbit',
                'author'           => 'J.R.R. Tolkien',
                'genres'           => ['Fantasy', 'Fiction', 'Adventure'],
                'page_number'      => 310,
                'synopsis'         => 'In a hole in the ground there lived a hobbit. Not a nasty, dirty, wet hole, filled with the ends of worms and an oozy smell, nor yet a dry, bare, sandy hole with nothing in it to sit down on or to eat: it was a hobbit-hole, and that means comfort. Written for J.R.R. Tolkien\'s own children, The Hobbit met with instant critical acclaim when it was first published in 1937.',
                'publication_year' => 1937,
                'publisher'        => 'George Allen & Unwin',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => 'The Lord of the Rings: The Fellowship of the Ring',
                'author'           => 'J.R.R. Tolkien',
                'genres'           => ['Fantasy', 'Fiction', 'Adventure'],
                'page_number'      => 423,
                'synopsis'         => 'One Ring to rule them all, One Ring to find them, One Ring to bring them all and in the darkness bind them. In ancient times the Rings of Power were crafted by the Elven-smiths, and Sauron, the Dark Lord, forged the One Ring, filling it with his own power so that he could rule all others.',
                'publication_year' => 1954,
                'publisher'        => 'George Allen & Unwin',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => 'Murder on the Orient Express',
                'author'           => 'Agatha Christie',
                'genres'           => ['Mystery', 'Fiction', 'Classic'],
                'page_number'      => 256,
                'synopsis'         => 'Just after midnight, a snowdrift stops the Orient Express in its tracks. The luxurious train is surprisingly full for the time of year, but by the morning it is one passenger fewer. An American tycoon lies dead in his compartment, stabbed a dozen times, his door locked from the inside. Isolated and with a killer in their midst, detective Hercule Poirot must identify the murderer.',
                'publication_year' => 1934,
                'publisher'        => 'Collins Crime Club',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => 'Pride and Prejudice',
                'author'           => 'Jane Austen',
                'genres'           => ['Romance', 'Fiction', 'Classic'],
                'page_number'      => 432,
                'synopsis'         => 'Since its immediate success in 1813, Pride and Prejudice has remained one of the most popular novels in the English language. Jane Austen called this brilliant work "her own darling child" and its vivacious heroine, Elizabeth Bennet, "as delightful a creature as ever appeared in print."',
                'publication_year' => 1813,
                'publisher'        => 'T. Egerton, Whitehall',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => 'One Hundred Years of Solitude',
                'author'           => 'Gabriel García Márquez',
                'genres'           => ['Fiction', 'Literary Fiction', 'Historical'],
                'page_number'      => 417,
                'synopsis'         => 'One Hundred Years of Solitude tells the story of the rise and fall of the mythical town of Macondo through the history of the Buendía family. It is a rich and brilliant chronicle of life and death, and the tragicomedy of humankind. In the noble, ridiculous, beautiful, and tawdry story of the Buendía family, one sees all of humanity.',
                'publication_year' => 1967,
                'publisher'        => 'Harper & Row',
                'language'         => 'English',
                'availability'     => 1,
            ],
            [
                'title'            => 'Bumi Manusia',
                'author'           => 'Pramoedya Ananta Toer',
                'genres'           => ['Fiction', 'Historical', 'Literary Fiction'],
                'page_number'      => 535,
                'synopsis'         => 'Set in the early 1900s during the Dutch colonial era in Java, this novel follows Minke, a young Javanese student who falls in love with Annelies, the daughter of a Dutch-Javanese woman and her Javanese concubine. Through their story, Pramoedya explores themes of colonialism, identity, justice, and humanity.',
                'publication_year' => 1980,
                'publisher'        => 'Hasta Mitra',
                'language'         => 'Indonesian',
                'availability'     => 1,
            ],
            [
                'title'            => 'Anak Semua Bangsa',
                'author'           => 'Pramoedya Ananta Toer',
                'genres'           => ['Fiction', 'Historical', 'Literary Fiction'],
                'page_number'      => 488,
                'synopsis'         => 'The second book in the Buru Quartet, continuing the story of Minke as he travels to Europe and witnesses how colonial power operates. He begins to understand the wider struggle for independence and human dignity that transcends individual experience.',
                'publication_year' => 1980,
                'publisher'        => 'Hasta Mitra',
                'language'         => 'Indonesian',
                'availability'     => 1,
            ],
            [
                'title'            => 'Laskar Pelangi',
                'author'           => 'Andrea Hirata',
                'genres'           => ['Fiction', 'Literary Fiction'],
                'page_number'      => 534,
                'synopsis'         => 'Laskar Pelangi follows ten children who call themselves the Rainbow Troops as they struggle to get an education in a poor mining town in Belitung Island, Indonesia. The story depicts their perseverance, friendship, and dreams despite extreme poverty and inadequate school facilities.',
                'publication_year' => 2005,
                'publisher'        => 'Bentang Pustaka',
                'language'         => 'Indonesian',
                'availability'     => 1,
            ],
            [
                'title'            => 'Sang Pemimpi',
                'author'           => 'Andrea Hirata',
                'genres'           => ['Fiction', 'Literary Fiction', 'Adventure'],
                'page_number'      => 292,
                'synopsis'         => 'The second novel in the Laskar Pelangi series. Ikal and his best friend Arai dream of studying in France and experience various adventures as they work as dock loaders while nurturing big dreams. This novel explores the power of dreams, friendship, and the sacrifices made in pursuit of a better future.',
                'publication_year' => 2006,
                'publisher'        => 'Bentang Pustaka',
                'language'         => 'Indonesian',
                'availability'     => 1,
            ],
            [
                'title'            => 'Hafalan Shalat Delisa',
                'author'           => 'Tere Liye',
                'genres'           => ['Fiction', 'Literary Fiction'],
                'page_number'      => 244,
                'synopsis'         => 'The story of Delisa, a six-year-old girl from Aceh who survives the 2004 Indian Ocean tsunami. As she struggles to recover from losing her family and her leg, she clings to the memory of memorising a prayer. A deeply moving novel about faith, loss, and the resilience of the human spirit.',
                'publication_year' => 2005,
                'publisher'        => 'Republika',
                'language'         => 'Indonesian',
                'availability'     => 1,
            ],
            [
                'title'            => 'Negeri Para Bedebah',
                'author'           => 'Tere Liye',
                'genres'           => ['Mystery', 'Fiction', 'Adventure'],
                'page_number'      => 360,
                'synopsis'         => 'Thomas is a financial consultant who gets entangled in a conspiracy involving corrupt bankers, shady politicians, and a financial crisis that threatens the nation. A fast-paced thriller that examines the dark underbelly of Indonesia\'s financial world and the battle between those who exploit the system and those who fight for justice.',
                'publication_year' => 2012,
                'publisher'        => 'Gramedia Pustaka Utama',
                'language'         => 'Indonesian',
                'availability'     => 1,
            ],
        ];

        foreach ($books as $data) {
            $authorModel = $authors[$data['author']];
            $genreIds    = array_map(fn($g) => $genres[$g]->id, $data['genres']);

            $book = Book::firstOrCreate(
                ['title' => $data['title'], 'author_id' => $authorModel->id],
                [
                    'page_number'      => $data['page_number'],
                    'synopsis'         => $data['synopsis'],
                    'publication_year' => $data['publication_year'],
                    'publisher'        => $data['publisher'],
                    'language'         => $data['language'],
                    'availability'     => $data['availability'],
                ]
            );

            $book->genres()->syncWithoutDetaching($genreIds);
        }
    }
}
