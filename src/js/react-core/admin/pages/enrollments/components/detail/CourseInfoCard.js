/**
 * Course Information Card Component
 */

import React from 'react';
import { getPostTypeEditUrl, getAdminPageUrl } from '../../../../../utility/url';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import { SplmsIcon } from "../../../../../components/SplmsIcon";

const CourseInfoCard = ({ enrollment }) => {
    if (!enrollment) return null;

    const courseEditUrl = getPostTypeEditUrl('sp-course', enrollment.course_id);
    const courseEnrollmentsUrl = getAdminPageUrl('splms-enrollments', { course_id: enrollment.course_id });

    return (
        <Card>
            <CardHeader>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                    <SplmsIcon mode="wp" icon="lesson" size={20} />
                    <h3 style={{ margin: 0 }}>{__('Course Information', 'skillpulse-lms')}</h3>
                </div>
            </CardHeader>
            <CardBody>
                <div style={{ display: 'flex', gap: '15px', alignItems: 'flex-start' }}>
                    <div style={{ flexShrink: 0 }}>
                        <div style={{
                            width: '80px',
                            height: '80px',
                            borderRadius: '8px',
                            backgroundColor: '#f0f0f1',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center'
                        }}>
                            <SplmsIcon mode="wp" icon="lesson" size={40} />
                        </div>
                    </div>
                    <div style={{ flex: 1 }}>
                        <h4 style={{ margin: '0 0 8px 0' }}>
                            <a
                                href={courseEditUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                style={{ textDecoration: 'none', color: 'inherit' }}
                            >
                                {enrollment.course_title || __('Unknown Course', 'skillpulse-lms')}
                                <span style={{ marginLeft: '5px', fontSize: '0.8em' }}>↗</span>
                            </a>
                        </h4>
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', fontSize: '0.9em', color: '#666' }}>
                            <div>
                                <strong>{__('Course ID:', 'skillpulse-lms')}</strong> #{enrollment.course_id}
                            </div>
                        </div>
                        <div style={{ marginTop: '12px', display: 'flex', gap: '8px' }}>
                            <a
                                href={courseEditUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                style={{
                                    fontSize: '0.875em',
                                    color: '#2271b1',
                                    textDecoration: 'none'
                                }}
                            >
                                {__('View Course', 'skillpulse-lms')} →
                            </a>
                            <span style={{ color: '#ddd' }}>|</span>
                            <a
                                href={courseEnrollmentsUrl}
                                style={{
                                    fontSize: '0.875em',
                                    color: '#2271b1',
                                    textDecoration: 'none'
                                }}
                            >
                                {__('View All Enrollments', 'skillpulse-lms')} →
                            </a>
                        </div>
                    </div>
                </div>
            </CardBody>
        </Card>
    );
};

export default CourseInfoCard;

