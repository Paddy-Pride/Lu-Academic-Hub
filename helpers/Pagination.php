<?php
/**
 * Pagination Class
 * Handles pagination logic
 */

class Pagination {
    private $current_page;
    private $per_page;
    private $total_items;
    private $total_pages;

    public function __construct($current_page = 1, $per_page = 10, $total_items = 0) {
        $this->current_page = max(1, intval($current_page));
        $this->per_page = max(1, intval($per_page));
        $this->total_items = max(0, intval($total_items));
        $this->total_pages = ceil($this->total_items / $this->per_page) ?: 1;
    }

    /**
     * Get offset for database query
     */
    public function getOffset() {
        return ($this->current_page - 1) * $this->per_page;
    }

    /**
     * Get limit for database query
     */
    public function getLimit() {
        return $this->per_page;
    }

    /**
     * Get current page
     */
    public function getCurrentPage() {
        return $this->current_page;
    }

    /**
     * Get total pages
     */
    public function getTotalPages() {
        return $this->total_pages;
    }

    /**
     * Get total items
     */
    public function getTotalItems() {
        return $this->total_items;
    }

    /**
     * Check if there's a next page
     */
    public function hasNextPage() {
        return $this->current_page < $this->total_pages;
    }

    /**
     * Check if there's a previous page
     */
    public function hasPreviousPage() {
        return $this->current_page > 1;
    }

    /**
     * Get next page number
     */
    public function getNextPage() {
        return $this->hasNextPage() ? $this->current_page + 1 : $this->total_pages;
    }

    /**
     * Get previous page number
     */
    public function getPreviousPage() {
        return $this->hasPreviousPage() ? $this->current_page - 1 : 1;
    }

    /**
     * Get pagination data
     */
    public function getData() {
        return [
            'current_page' => $this->current_page,
            'per_page' => $this->per_page,
            'total_items' => $this->total_items,
            'total_pages' => $this->total_pages,
            'has_next' => $this->hasNextPage(),
            'has_previous' => $this->hasPreviousPage(),
            'next_page' => $this->getNextPage(),
            'previous_page' => $this->getPreviousPage()
        ];
    }

    /**
     * Generate pagination links
     */
    public function getLinks($url_pattern = '?page=%d') {
        $links = [];
        
        if ($this->hasPreviousPage()) {
            $links['first'] = sprintf($url_pattern, 1);
            $links['previous'] = sprintf($url_pattern, $this->getPreviousPage());
        }
        
        if ($this->hasNextPage()) {
            $links['next'] = sprintf($url_pattern, $this->getNextPage());
            $links['last'] = sprintf($url_pattern, $this->total_pages);
        }
        
        return $links;
    }

    /**
     * Get page range
     */
    public function getPageRange($range = 5) {
        $start = max(1, $this->current_page - floor($range / 2));
        $end = min($this->total_pages, $start + $range - 1);
        $start = max(1, $end - $range + 1);
        
        return range($start, $end);
    }
}
?>