<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MongoDBService
{
    private string $uri;
    private HttpClientInterface $httpClient;
    private string $dbName = 'skillswap';
    private array $dataStore = [];

    public function __construct(
        ParameterBagInterface $parameterBag,
        HttpClientInterface $httpClient
    ) {
        // Get the MongoDB URI from environment variables or parameters
        $this->uri = $parameterBag->get('mongodb_uri');
        $this->httpClient = $httpClient;
        $this->initializeDataStore();
    }

    /**
     * Initialize in-memory data store for simulation
     */
    private function initializeDataStore(): void
    {
        // Create collections that we need
        $this->dataStore = [
            'users' => [],
            'sessions' => [],
            'messages' => [],
            'reviews' => [],
            'skill_categories' => []
        ];
    }

    /**
     * Get all documents from a collection
     *
     * @param string $collectionName
     * @return array
     */
    public function getCollection(string $collectionName): array
    {
        return $this->dataStore[$collectionName] ?? [];
    }

    /**
     * Find a document by ID
     *
     * @param string $collectionName
     * @param string $id
     * @return array|null
     */
    public function findById(string $collectionName, string $id): ?array
    {
        if (!isset($this->dataStore[$collectionName])) {
            return null;
        }

        foreach ($this->dataStore[$collectionName] as $document) {
            if ($document['_id'] === $id) {
                return $document;
            }
        }

        return null;
    }

    /**
     * Find documents by criteria
     *
     * @param string $collectionName
     * @param array $criteria
     * @return array
     */
    public function findBy(string $collectionName, array $criteria): array
    {
        if (!isset($this->dataStore[$collectionName])) {
            return [];
        }

        if (empty($criteria)) {
            return $this->dataStore[$collectionName];
        }

        $results = [];
        foreach ($this->dataStore[$collectionName] as $document) {
            $match = true;
            foreach ($criteria as $key => $value) {
                // Simple criteria matching
                if (!isset($document[$key]) || $document[$key] !== $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $results[] = $document;
            }
        }

        return $results;
    }

    /**
     * Insert a document
     *
     * @param string $collectionName
     * @param array $document
     * @return string
     */
    public function insertDocument(string $collectionName, array $document): string
    {
        if (!isset($this->dataStore[$collectionName])) {
            $this->dataStore[$collectionName] = [];
        }

        // Generate a unique ID if not provided
        if (!isset($document['_id'])) {
            $document['_id'] = bin2hex(random_bytes(12)); // Simulate MongoDB ObjectId
        }

        $this->dataStore[$collectionName][] = $document;
        return $document['_id'];
    }

    /**
     * Update a document
     *
     * @param string $collectionName
     * @param string $id
     * @param array $document
     * @return bool
     */
    public function updateDocument(string $collectionName, string $id, array $document): bool
    {
        if (!isset($this->dataStore[$collectionName])) {
            return false;
        }

        foreach ($this->dataStore[$collectionName] as $index => $existingDocument) {
            if ($existingDocument['_id'] === $id) {
                // Remove _id from update data if it exists
                if (isset($document['_id'])) {
                    unset($document['_id']);
                }

                // Update document
                $this->dataStore[$collectionName][$index] = array_merge($existingDocument, $document);
                return true;
            }
        }

        return false;
    }

    /**
     * Delete a document
     *
     * @param string $collectionName
     * @param string $id
     * @return bool
     */
    public function deleteDocument(string $collectionName, string $id): bool
    {
        if (!isset($this->dataStore[$collectionName])) {
            return false;
        }

        foreach ($this->dataStore[$collectionName] as $index => $document) {
            if ($document['_id'] === $id) {
                array_splice($this->dataStore[$collectionName], $index, 1);
                return true;
            }
        }

        return false;
    }
} 