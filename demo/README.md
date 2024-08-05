# Flagship Demo PHP Application

Welcome to the Flagship Demo PHP symfony Application. This application is a demonstration of how to use Flagship for feature flagging and A/B testing in a PHP symfony application.

This implementation is based on two use cases:

1. **Fs demo toggle use case**: This feature toggle campaign enables a discount for VIP users.
2. **Fs demo A/B Test use case**: This A/B test campaign allows you to test the color of the 'Add to Cart' button.

## Prerequisites

Before you begin, ensure you have met the following requirements:

- You have installed PHP version 8.1 or higher.
- You have installed [Composer](https://getcomposer.org/download/).
- You have a [Flagship account](https://www.abtasty.com) to obtain your environment ID and API key.
- The cURL extension is enabled in your PHP configuration.
- The JSON extension is enabled in your PHP configuration.

## Getting Started

### Running the Application Locally

Follow these steps to get up and running quickly on your local machine:

1. **Install Symfony CLI**: Ensure you have the Symfony CLI installed. You can download it from the Symfony website.

2. **Clone the Repository**: Clone the repository containing the Flagship demo ASP.NET Core application.

    ```bash
    git clone https://github.com/flagship-io/flagship-php-sdk.git
    cd flagship-dotnet-sdk/demo
    ```

3. **Restore Dependencies**: Restore the required dependencies for the project.

    ```bash
    composer install
    ```

4. **Run the Application**: Run the application locally.

    ```bash
    symfony server:start
    ```

The application will be accessible at `http://localhost:8000`.

### Running the Application in Docker

If you prefer to use Docker, you can build and run the application using the provided shell script:

```bash
chmod +x run-docker.sh && ./run-docker.sh
```

## API Endpoints

This application provides the following API endpoints:

### GET /item

This endpoint fetches an item and applies any feature flags for the visitor.

Example:

```bash
curl http://localhost:5000/item
```

This will return a JSON object with the item details and any modifications applied by feature flags.

### POST /add-to-cart

This endpoint simulates adding an item to the cart and sends a hit to track the action.

Example:

 ```bash
 curl -X POST http://localhost:5000/add-to-cart
 ```

 This will send a hit to track the "add-to-cart-clicked" action for the visitor.
