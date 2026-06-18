/**
 * Enrollment Details Card Component
 */

import React from 'react';
import { getAdminUrl } from '../../../../../utility/url';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import { SplmsIcon } from "../../../../../components/SplmsIcon";
import { formatDate } from '../../../../../utility/helper';
import { MethodBadge } from '../EnrollmentBadges';

const EnrollmentDetailsCard = ({ enrollment }) => {
    if (!enrollment) return null;

    const orderUrl = enrollment.order_id 
        ? getAdminPageUrl('splms-orders', { order_id: enrollment.order_id })
        : null;

    return (
        <Card>
            <CardHeader>
                <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                    <SplmsIcon mode="wp" icon="info" size={20} />
                    <h3 style={{ margin: 0 }}>{__('Enrollment Details', 'skillpulse-lms')}</h3>
                </div>
            </CardHeader>
            <CardBody>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '20px' }}>
                    <div>
                        <div style={{ marginBottom: '12px' }}>
                            <strong style={{ display: 'block', marginBottom: '4px', color: '#666' }}>
                                {__('Enrollment Date:', 'skillpulse-lms')}
                            </strong>
                            <span>{formatDate(enrollment.enrolled_at)}</span>
                        </div>
                        <div style={{ marginBottom: '12px' }}>
                            <strong style={{ display: 'block', marginBottom: '4px', color: '#666' }}>
                                {__('Enrollment Method:', 'skillpulse-lms')}
                            </strong>
                            <MethodBadge method={enrollment.enrollment_method} />
                        </div>
                        <div style={{ marginBottom: '12px' }}>
                            <strong style={{ display: 'block', marginBottom: '4px', color: '#666' }}>
                                {__('Status:', 'skillpulse-lms')}
                            </strong>
                            <span>{enrollment.status || __('N/A', 'skillpulse-lms')}</span>
                        </div>
                    </div>
                    <div>
                        {enrollment.completed_at && (
                            <div style={{ marginBottom: '12px' }}>
                                <strong style={{ display: 'block', marginBottom: '4px', color: '#666' }}>
                                    {__('Completion Date:', 'skillpulse-lms')}
                                </strong>
                                <span>{formatDate(enrollment.completed_at)}</span>
                            </div>
                        )}
                        {enrollment.last_activity && (
                            <div style={{ marginBottom: '12px' }}>
                                <strong style={{ display: 'block', marginBottom: '4px', color: '#666' }}>
                                    {__('Last Activity:', 'skillpulse-lms')}
                                </strong>
                                <span>{enrollment.last_activity}</span>
                            </div>
                        )}
                        {enrollment.time_spent && (
                            <div style={{ marginBottom: '12px' }}>
                                <strong style={{ display: 'block', marginBottom: '4px', color: '#666' }}>
                                    {__('Time Spent:', 'skillpulse-lms')}
                                </strong>
                                <span>{enrollment.time_spent}</span>
                            </div>
                        )}
                        {enrollment.order_id && (
                            <div style={{ marginBottom: '12px' }}>
                                <strong style={{ display: 'block', marginBottom: '4px', color: '#666' }}>
                                    {__('Order ID:', 'skillpulse-lms')}
                                </strong>
                                {orderUrl ? (
                                    <a href={orderUrl} style={{ color: '#2271b1', textDecoration: 'none' }}>
                                        #{enrollment.order_id} →
                                    </a>
                                ) : (
                                    <span>#{enrollment.order_id}</span>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </CardBody>
        </Card>
    );
};

export default EnrollmentDetailsCard;

