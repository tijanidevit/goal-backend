# GoalPilot Backend API

The GoalPilot backend is a robust Laravel 12 API designed to manage personal financial goals, track milestones, evaluate goal health, and provide realistic forecasts based on user financial profiles.

## Core Features

- **Authentication:** Secure user registration, login, and token management using Laravel Sanctum.
- **Goal Management:** Create, read, update, and delete financial goals. Support for designating a primary goal.
- **Financial Profiling:** Manage monthly income, expenses, and debts to calculate total available savings capacity.
- **Forecast Engine:** Dynamically calculates current savings pace, required monthly savings, and projected completion dates.
- **Health Engine:** Evaluates if a goal is "On Track", "Needs Attention", or "At Risk" based on the user's savings pace versus required pace.
- **Milestone Tracking:** Automatically generates and tracks milestones (e.g., 25%, 50%, 75% achieved) for each goal.
- **Contributions:** Log financial contributions against specific goals.

## Tech Stack

- **Framework:** Laravel 12 (PHP)
- **Database:** SQLite (default for local development) / MySQL / PostgreSQL
- **Testing:** PHPUnit

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js & NPM (for asset compilation if needed)

### Installation

1. **Clone the repository and install dependencies:**
   ```bash
   composer install
   ```

2. **Environment Setup:**
   Copy the example environment file and generate an application key.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration:**
   By default, the application uses SQLite. Ensure you have an empty `database/database.sqlite` file created, or configure your `.env` for MySQL/PostgreSQL.
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

4. **Serve the Application:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8006
   ```

## Testing

The backend includes a comprehensive suite of Feature and Unit tests covering the core engines (Forecasting, Health, Milestones) and API endpoints.

```bash
php artisan test
```

## API Endpoints

- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/dashboard` - Retrieve aggregated dashboard data, including smart insights and forecasts
- `GET /api/goals` - List all goals
- `POST /api/goals` - Create a new goal
- `POST /api/goals/{id}/contributions` - Add a contribution to a goal
- `GET /api/financial-profile` - Retrieve financial profile

*All endpoints except login/register require an `Authorization: Bearer <token>` header.*
