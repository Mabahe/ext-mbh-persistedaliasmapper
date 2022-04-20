<?php

declare(strict_types=1);

namespace Mabahe\MbhPersistedaliasmapper\Routing\Aspect;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendGroupRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper as CorePersistedAliasMapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Classic usage when using a "URL segment" (e.g. slug) field within a database table.
 *
 * Example:
 *   routeEnhancers:
 *     EventsPlugin:
 *       type: Extbase
 *       extension: Events2
 *       plugin: Pi1
 *       routes:
 *         - { routePath: '/events/{event}', _controller: 'Event::detail', _arguments: {'event': 'event_name'}}
 *       defaultController: 'Events2::list'
 *       aspects:
 *         event:
 *           type: PersistedAliasMapper
 *           tableName: 'tx_events2_domain_model_event'
 *           routeFieldName: 'path_segment'
 *           routeValuePrefix: '/'
 */
class PersistedAliasMapper extends CorePersistedAliasMapper
{
    protected function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($this->tableName)
            ->from($this->tableName);
        $queryBuilder->setRestrictions(
            GeneralUtility::makeInstance(FrontendRestrictionContainer::class, $this->context)
        );
        // Frontend Groups are not available at this time (initialized via TSFE->determineId)
        // So this must be excluded to allow access restricted records
        $queryBuilder->getRestrictions()->removeByType(FrontendGroupRestriction::class);
        if ($this->settings['includeHidden']) {
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        }
        return $queryBuilder;
    }
}
