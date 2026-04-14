import { __ } from '@wordpress/i18n';
import { Button, Icon, Dropdown, MenuGroup, MenuItem } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import BrandLogo from './BrandLogo';
import { SplmsIcon } from './SplmsIcon';

const AdminHeader = ({ 
    title, 
    helpUrl = "https://skillpulselms.com/docs",
    helpText = __("Help & Documentation", "skillpulse-lms"),
    helpType = 'button', // 'filter' | 'button' - controls which button to render
    showFilters = false, // Use helpType='filter' instead
    onFilterToggle = null,
    filtersVisible = false,
    tabs = [],
    activeTab = null,
    onTabChange = null,
    customActions = null,
    // New dynamic props
    enhancedTitle = false,
    primaryAction = null,
    secondaryActions = [],
    dropdownActions = [],
    className = ""
}) => {
    // Determine which type to use (priority: helpType > showFilters)
    const buttonType = helpType || (showFilters ? 'filter' : 'button');
    
    // Default filter action if helpType is 'filter' or showFilters is true
    const handleFilterToggle = () => {
        if (onFilterToggle) {
            onFilterToggle();
        }
    };

    const renderHelpButton = () => (
        <Button
            className="splms-header-icon-help-button"
            onClick={() => {
                if (helpUrl) {
                    window.open(helpUrl, "_blank", "noopener,noreferrer");
                }
            }}
            aria-label={__("Get help with SkillPulse LMS", "skillpulse-lms")}
            disabled={!helpUrl}
        >
            <SplmsIcon name="help" size={20} />
            <span className="splms-header-icon-help-text">
                {helpText || __("Help & Documentation", "skillpulse-lms")}
            </span>
        </Button>
    );

    const renderFilterButton = () => (
        <Button
            className="splms-header-icon-help-button"
            onClick={handleFilterToggle}
            disabled={!onFilterToggle}
            aria-label={filtersVisible ? __("Hide Filters", "skillpulse-lms") : __("Show Filters", "skillpulse-lms")}
        >
            <SplmsIcon name="filter" size={20} />
            <span className="splms-header-icon-help-text">
                {filtersVisible ? __("Hide Filters", "skillpulse-lms") : __("Show Filters", "skillpulse-lms")}
            </span>
        </Button>
    );

    // Render the appropriate button based on helpType
    const renderActionButton = () => {
        if (buttonType === 'filter') {
            return renderFilterButton();
        } else if (buttonType === 'button') {
            return renderHelpButton();
        }
        return null;
    };

    const renderPrimaryAction = () => {
        if (!primaryAction) return null;
        
        const {
            text,
            href,
            onClick,
            icon = 'plus',
            className: actionClassName = 'splms-add-button'
        } = primaryAction;

        const buttonProps = {
            className: actionClassName,
            children: (
                <>
                    <SplmsIcon name={icon || "plus"} size={16} />
                    {text}
                </>
            )
        };

        if (href) {
            return <Button isPrimary href={href} {...buttonProps} />;
        }

        if (onClick) {
            return <Button isPrimary onClick={onClick} {...buttonProps} />;
        }

        return null;
    };

    const renderSecondaryActions = () => {
        if (!secondaryActions || secondaryActions.length === 0) return null;

        return secondaryActions.map((action, index) => {
            const {
                text,
                href,
                onClick,
                icon,
                className: actionClassName = 'splms-secondary-button'
            } = action;

            const buttonProps = {
                key: index,
                className: actionClassName,
                children: (
                    <>
                        {icon && <SplmsIcon name={icon} size={16} />}
                        {text}
                    </>
                )
            };

            if (href) {
                return <Button isSecondary href={href} {...buttonProps} />;
            }

            if (onClick) {
                return <Button isSecondary onClick={onClick} {...buttonProps} />;
            }

            return null;
        });
    };

    const renderDropdownActions = () => {
        if (!dropdownActions || Object.keys(dropdownActions).length === 0) return null;

        const {
            triggerText,
            triggerIcon = 'admin-generic',
            items = [],
            className: dropdownClassName = 'splms-dropdown'
        } = dropdownActions;

        return (
            <Dropdown
                className={dropdownClassName}
                contentClassName="splms-dropdown-content"
                renderToggle={({ isOpen, onToggle }) => (
                    <Button
                        isSecondary
                        onClick={onToggle}
                        aria-expanded={isOpen}
                        className="splms-secondary-button"
                    >
                        <SplmsIcon name={triggerIcon || "plus"} size={16} />
                        {triggerText}
                    </Button>
                )}
                renderContent={() => (
                    <MenuGroup>
                        {items.map((item, index) => (
                            <MenuItem
                                key={index}
                                onClick={item.onClick}
                                className="splms-dropdown-item"
                            >
                                {item.icon && <SplmsIcon name={item.icon} size={16} />}
                                {item.text}
                            </MenuItem>
                        ))}
                    </MenuGroup>
                )}
            />
        );
    };

    const renderDynamicActions = () => {
        const hasDynamicActions = primaryAction || secondaryActions.length > 0 || Object.keys(dropdownActions).length > 0;
        
        if (!hasDynamicActions && !customActions) return null;

        return (
            <div className="splms-header-actions">
                {primaryAction && renderPrimaryAction()}
                {Object.keys(dropdownActions).length > 0 && renderDropdownActions()}
                {secondaryActions.length > 0 && renderSecondaryActions()}
                {customActions && customActions}
            </div>
        );
    };

    const renderTabs = () => {
        if (!tabs || tabs.length === 0) return null;
        
        return (
            <nav className="splms-header-nav" role="navigation" aria-label={__("Page navigation", "skillpulse-lms")}>
                <ul role="tablist">
                    {tabs.map(tab => (
                        <li 
                            key={tab.id} 
                            className={activeTab === tab.id ? 'active' : ''} 
                            role="presentation"
                        >
                            <a 
                                onClick={(e) => {
                                    e.preventDefault();
                                    if (onTabChange) {
                                        onTabChange(tab.id);
                                    }
                                }} 
                                href={`#${tab.id}`}
                                role="tab"
                                aria-selected={activeTab === tab.id}
                                aria-controls={`panel-${tab.id}`}
                                tabIndex={activeTab === tab.id ? 0 : -1}
                            >
                                {tab.title}
                            </a>
                        </li>
                    ))}
                </ul>
            </nav>
        );
    };

    const headerClassName = [
        enhancedTitle ? 'enhanced-title' : '',
        className
    ].filter(Boolean).join(' ');

    return (
        <header 
            id="splms-admin-header" 
            className={headerClassName}
            role="banner"
        >
            <div className="splms-header-logo">
                <div className="logo inline">
                    <BrandLogo />
                    <div className="splms-header-separator" />
                    <h1 className="splms-header-title inline">
                        {title}
                    </h1>
                </div>
                <div className="splms-header-icon">
                    <div className="splms-header-icon-help">
                        {renderDynamicActions()}
                        {/* Render action button based on helpType prop */}
                        {!customActions && !primaryAction && secondaryActions.length === 0 && Object.keys(dropdownActions).length === 0 && renderActionButton()}
                    </div>
                </div>
            </div>
            {renderTabs()}
        </header>
    );
};

export default AdminHeader; 