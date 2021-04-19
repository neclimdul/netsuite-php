<?php

namespace NetSuite;

use NetSuite\Exception\StatusFailure;
use NetSuite\Classes\SearchMoreWithIdRequest;
use NetSuite\Classes\SearchRecord;
use NetSuite\Classes\SearchRequest;
use NetSuite\Classes\SearchResult;

/**
 * NetSuite search iterator.
 *
 * This is a iterator that allows seamless iteration over all results of a
 * search. This is done by handling "search more" requests seamlessly as needed
 * behind the scenes.
 *
 * Because this iterator is doing SOAP requests as needed, use of the iterator
 * needs to be wrapped in error handling.
 *
 * It doesn't seem possible to sort the results so you may have to process
 * all the paged results from start to end in one go...
 *
 * @see https://stackoverflow.com/q/30975586
 *
 *
 * Example:
 * ```
 * $iterator = new SearchIterator($service, $search);
 * try {
 *   foreach ($iterator as $record) {
 *     // process results
 *   }
 * }
 * catch (\SoapFault $e) {
 *   // Handle exception
 * }
 * catch (\Apqc\NetSuite\Exception\StatusFailure $e) {
 *   // Handle exception
 * }
 * ```
 *
 * For loops are more than enough to interact with this iterator but it can also
 * be useful connected with a generator and the \nikic\iter library.
 *
 * Example:
 * ```
 * fetchCustomer($date) {
 *   $search = new CustomerSearchBasic();
 *   $search->lastModifiedDate = new SearchDateField();
 *   $search->lastModifiedDate->operator = SearchDateFieldOperator::after;
 *   $search->lastModifiedDate->searchValue = date(DATE_ISO8601, $date);
 *   $iterator = new SearchIterator($service, $search);
 *   foreach ($iterator as $record) {
 *     yield convertCustomer($record);
 *   }
 * }
 * // This is a pretty weird filter not to include in your search but easy to read.
 * $mylist = \iter\filter(fn($customer) => $customer->isActive, fetchCustomer('2020-09-09'));
 * foreach ($mylist as $customer) {
 *   print($customer->companyName);
 * }
 * ```
 */
class SearchIterator implements \Iterator, \Countable {

    /**
     * @var \NetSuite\NetSuiteService
     */
    protected $netSuiteService;

    /**
     * @var \NetSuite\Classes\SearchRecord
     */
    protected $search;

    /**
     * Storage for search results.
     *
     * @var \ArrayObject
     */
    protected $results;

    /**
     * An internal iterator to the array storage for easier implementation.
     *
     * @var \ArrayIterator
     */
    protected $resultsIterator;

    /**
     * The search identifier for paging through the search results.
     *
     * @var string
     */
    protected $searchId;

    /**
     * The last page the search has fetched sequentially.
     *
     * @var int
     */
    protected $page = 0;

    /**
     * The last page of the search results.
     *
     * @var int
     */
    protected $maxPage;

    /**
     * The total number of results in the results set.
     *
     * @var int
     */
    protected $totalRecords;

    /**
     * The size of the page used to page.
     *
     * This should be used consistently across all page requests or the page
     * numbering won't work correctly.
     *
     * @var int|mixed
     */
    private $pageSize;

    public function __construct(NetSuiteService $netSuiteService, SearchRecord $search, $pageSize = 50) {
        $this->netSuiteService = $netSuiteService;
        $this->search = $search;
        $this->results = new \ArrayObject();
        $this->resultsIterator = $this->results->getIterator();
        $this->pageSize = $pageSize;
    }

    /**
     * {@inheritDoc}
     */
    public function current() {
        $this->init();
        return $this->resultsIterator->current();
    }

    /**
     * {@inheritDoc}
     */
    public function next() {
        $this->init();
        $this->resultsIterator->next();
        // If we run into the end, request more.
        if (!$this->resultsIterator->valid()) {
            $this->searchMore();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function key() {
        $this->init();
        return $this->resultsIterator->key();
    }

    /**
     * {@inheritDoc}
     */
    public function valid() {
        $this->init();
        return $this->resultsIterator->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind() {
        $this->init();
        $this->resultsIterator->rewind();
    }

    /**
     * {@inheritDoc}
     */
    public function count() {
        $this->init();
        return $this->totalRecords;
    }

    /**
     * Make sure the internal properties are initialized from the first request.
     *
     * @throws \SoapFault
     */
    private function init(): void {
        if (!isset($this->searchId)) {
            $this->initialSearch();
        }
    }

    /**
     * Perform the initial search.
     *
     * @throws \SoapFault
     * @throws \NetSuite\Exception\StatusFailure
     */
    private function initialSearch(): void {
        $search_request = new SearchRequest();
        $search_request->searchRecord = $this->search;
        $this->netSuiteService->setSearchPreferences(TRUE, $this->pageSize);
        try {
            $result = $this->netSuiteService->search($search_request);
        } finally {
            $this->netSuiteService->clearSearchPreferences();
        }
        $this->processResults($result->searchResult);
    }

    /**
     * Fetch more results from the search.
     *
     * @throws \SoapFault
     * @throws \NetSuite\Exception\StatusFailure
     */
    private function searchMore(): void {
        if ($this->page < $this->maxPage) {
            $request = new SearchMoreWithIdRequest();
            $request->pageIndex = $this->page + 1;
            $request->searchId = $this->searchId;
            $this->netSuiteService->setSearchPreferences(TRUE, $this->pageSize);
            try {
                $result = $this->netSuiteService->searchMoreWithId($request);
            } finally {
                $this->netSuiteService->clearSearchPreferences();
            }
            $this->processResults($result->searchResult);
        }
    }

    /**
     * Process search results and handle failures.
     *
     * @param \NetSuite\Classes\SearchResult $result
     *   A search result object.
     *
     * @throws \NetSuite\Exception\StatusFailure
     */
    private function processResults(SearchResult $result): void {
        if ($result->status->isSuccess) {
            $this->updateState($result);
            if ($result->recordList->record) {
                foreach ($result->recordList->record as $item) {
                    $this->results->append($item);
                }
            }
        }
        elseif (!$result->status->isSuccess) {
            throw new StatusFailure($result->status);
        }
    }

    /**
     * Update internal state tracking the search position.
     *
     * @param \NetSuite\Classes\SearchResult $result
     *   A search result object.
     */
    private function updateState(SearchResult $result): void {
        assert(!!$result->searchId, 'Missing searchId. This could lead to infinite loops.');
        $this->page = $result->pageIndex;
        $this->searchId = $result->searchId;
        $this->maxPage = $result->totalPages;
        $this->totalRecords = $result->totalRecords;
    }

}
