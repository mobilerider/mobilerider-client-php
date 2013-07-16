<?php 

namespace Mr\Api\Collection;

class AbstractPaginator
{
	protected $_page;
    protected $_total;
	protected $_pageTotal;

    protected function validatePage($page)
    {
        return min($this->_pageTotal, max(0, $page));
    }

    public function setCurrentPage($page)
    {
        $this->_page = $this->validatePage($page);
    }

    public function getCurrentPage()
    {
        return $this->_page;
    }

    public function increasePage()
    {
        if ($this->hasNextPage()) {
            return ++$this->_page;
        }
    }

    public function decreasePage()
    {
        if ($this->hasPreviousPage()) {
            return --$this->_page;
        }
    }

    public function hasNextPage()
    {
        return $this->_page < $this->_pageTotal;
    }

    public function hasPreviousPage()
    {
        return $this->_page > 1;
    }
}