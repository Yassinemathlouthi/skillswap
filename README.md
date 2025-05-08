# SkillSwap

SkillSwap is a platform that enables users to exchange skills for free. Users can offer their expertise in various areas and request to learn from others in an informal, non-monetary exchange.

## Features

- User registration and authentication
- Skill categorization and browsing
- Session scheduling between users
- Messaging between users
- Ratings and reviews after completed sessions
- Admin dashboard for moderation

## Technical Details

This application is built with:

- Symfony 7.2 PHP framework
- In-memory MongoDB simulation (no actual MongoDB required)
- Bootstrap 5 for UI components
- AlpineJS for interactive components
- Font Awesome icons

## Setup Instructions

### Prerequisites

- PHP 8.3+
- Composer
- Symfony CLI

### Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/skillswap.git
   cd skillswap
   ```

2. Install dependencies:
   ```
   composer install
   npm install
   ```

3. Build assets:
   ```
   npm run build
   ```

4. Load sample data:
   ```
   php bin/console app:seed-data
   ```

5. Start the Symfony server:
   ```
   symfony server:start
   ```

6. Open your browser and navigate to `https://localhost:8000`

### Sample Users

The following users are created by the data seeder:

| Username | Email | Password | Role |
|----------|-------|----------|------|
| admin | admin@example.com | admin123 | ADMIN |
| john | john@example.com | password123 | USER |
| jane | jane@example.com | password123 | USER |
| michael | michael@example.com | password123 | USER |
| emma | emma@example.com | password123 | USER |

## MongoDB Simulation

This project uses an in-memory MongoDB simulation instead of requiring a real MongoDB installation. The simulation is provided by the `MongoDBService` class which implements the basic MongoDB operations needed for this application:

- Collection retrieval
- Document insertion
- Document retrieval by ID
- Document updates
- Document deletion
- Querying by criteria

The data is stored in memory during the application's lifetime and is automatically seeded with sample data when you run the `app:seed-data` command.

## For MongoDB Production Use

If you want to use a real MongoDB instance instead of the simulation:

1. Install the MongoDB PHP extension
   - For Windows: Add the extension to php.ini: `extension=mongodb`
   - For Linux: `sudo pecl install mongodb`

2. Update the composer.json to include:
   ```json
   "require": {
       "ext-mongodb": "*",
       "doctrine/mongodb-odm-bundle": "^4.4"
   }
   ```

3. Run `composer update`

4. Configure the MongoDB connection in your .env:
   ```
   MONGODB_URI=mongodb://username:password@localhost:27017/skillswap
   ```

5. Replace the simulated MongoDBService with the actual MongoDB ODM implementation.

## License

This project is licensed under the MIT License - see the LICENSE file for details. 