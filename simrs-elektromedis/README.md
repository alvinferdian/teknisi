# SIM RS - Elektromedis

## Overview
SIM RS - Elektromedis is a web application designed to manage the profiles of medical technicians (teknisi) in a hospital setting. The application allows users to add, edit, and delete technician records, as well as manage their associated data such as contact information and photos.

## Project Structure
The project is organized into several directories and files, each serving a specific purpose:

- **public/**: Contains publicly accessible files, including the entry point for the application and assets like CSS and JavaScript.
  - **index.php**: The main entry point for the application.
  - **.htaccess**: Configuration file for URL rewriting and server settings.
  - **css/**: Directory for CSS styles.
  - **js/**: Directory for JavaScript files.
  - **uploads/**: Directory for storing uploaded files.

- **src/**: Contains the core application logic, including controllers, models, views, and configuration.
  - **Controller/**: Contains controller classes that handle requests and responses.
    - **TeknisiController.php**: Manages teknisi-related actions.
  - **Model/**: Contains model classes that represent the data structure.
    - **Teknisi.php**: Represents the teknisi data and interacts with the database.
  - **View/**: Contains view templates for rendering the user interface.
    - **templates/**: Directory for layout and view templates.
      - **layout.php**: Main layout template.
      - **header.php**: Header section of the layout.
      - **footer.php**: Footer section of the layout.
      - **teknisi/**: Directory for teknisi-specific views.
        - **index.php**: Displays the list of teknisi.
        - **add.php**: Form for adding a new teknisi.
        - **edit.php**: Form for editing an existing teknisi.
  - **Config/**: Contains configuration files.
    - **config.php**: Application configuration settings.
  - **Helpers/**: Contains helper functions.
    - **view.php**: Functions for rendering views.

- **routes/**: Contains route definitions for the application.
  - **web.php**: Maps URLs to controller actions.

- **database/**: Contains database-related files.
  - **migrations/**: Directory for database migration scripts.
    - **2025_01_01_create_teknisi_table.sql**: Migration script for creating the teknisi table.

- **tests/**: Contains test files for the application.
  - **Feature/**: Directory for feature tests.
    - **TeknisiTest.php**: Tests for teknisi functionality.

- **composer.json**: Composer configuration file for managing dependencies.

- **.env.example**: Template for environment variables.

## Installation
1. Clone the repository to your local machine.
2. Navigate to the project directory.
3. Install dependencies using Composer:
   ```
   composer install
   ```
4. Configure your environment variables by copying `.env.example` to `.env` and updating the values as needed.
5. Run the database migrations:
   ```
   php artisan migrate
   ```
6. Start the local development server:
   ```
   php -S localhost:8000 -t public
   ```

## Usage
- Access the application in your web browser at `http://localhost:8000`.
- Use the navigation to manage teknisi records, including adding, editing, and deleting entries.

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for more details.