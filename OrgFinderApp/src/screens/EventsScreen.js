import React, { useState, useEffect, useCallback } from 'react';
import {
    View, Text, TextInput, TouchableOpacity, StyleSheet,
    FlatList, Image, ActivityIndicator, RefreshControl, Modal, ScrollView,
    useWindowDimensions,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import api from '../api/client';

export default function EventsScreen({ navigation }) {
    const { height } = useWindowDimensions();
    const headerSpacing = Math.round(height * 0.03);

    const [events, setEvents]         = useState([]);
    const [orgs, setOrgs]             = useState([]);
    const [loading, setLoading]       = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch]         = useState('');
    const [selectedOrg, setSelectedOrg] = useState(null); // { id, name }
    const [showOrgModal, setShowOrgModal] = useState(false);

    // Fetch org list once for the filter dropdown
    useEffect(() => {
        api.get('/organizations').then(res => {
            setOrgs(res.data.organizations.map(o => ({ id: o.id, name: o.name })));
        }).catch(() => {});
    }, []);

    const loadEvents = useCallback(async () => {
        try {
            const params = {};
            if (search.trim()) params.search = search.trim();
            if (selectedOrg) params.org_id = selectedOrg.id;
            const res = await api.get('/events/upcoming', { params });
            setEvents(res.data.events);
        } catch {}
        finally { setLoading(false); setRefreshing(false); }
    }, [search, selectedOrg]);

    useEffect(() => { setLoading(true); loadEvents(); }, [selectedOrg]);

    const renderEvent = ({ item }) => (
        <TouchableOpacity
            style={styles.eventCard}
            onPress={() => navigation.navigate('EventDetail', { id: item.id })}
            activeOpacity={0.88}
        >
            {/* Square poster on the left */}
            {item.poster
                ? <Image source={{ uri: item.poster }} style={styles.poster} />
                : <View style={styles.posterPlaceholder} />
            }

            {/* Details on the right */}
            <View style={styles.eventInfo}>
                <Text style={styles.eventTitle} numberOfLines={3}>{item.title}</Text>
                <View style={styles.metaRow}>
                    <Ionicons name="calendar-outline" size={13} color="#000000" />
                    <Text style={styles.metaText}>{item.date}</Text>
                </View>
                {item.time ? (
                    <View style={styles.metaRow}>
                        <Ionicons name="time-outline" size={13} color="#000000" />
                        <Text style={styles.metaText}>{item.time}</Text>
                    </View>
                ) : null}
                {item.venue ? (
                    <View style={styles.metaRow}>
                        <Ionicons name="location-outline" size={13} color="#000000" />
                        <Text style={styles.metaText}>{item.venue}</Text>
                    </View>
                ) : null}
                <TouchableOpacity
                    style={styles.viewDetailsWrap}
                    onPress={() => navigation.navigate('EventDetail', { id: item.id })}
                >
                    <Text style={styles.viewDetailsLink}>View Details</Text>
                </TouchableOpacity>
            </View>
        </TouchableOpacity>
    );

    return (
        <View style={styles.root}>
            <LinearGradient colors={['#7CB9FF', '#4A6CF7']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.header}>
                <SafeAreaView>
                    <View style={[styles.headerRow, { marginBottom: headerSpacing }]}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                            <Text style={styles.backIcon}>‹</Text>
                        </TouchableOpacity>
                        <Text style={styles.headerTitle}>Upcoming Events</Text>
                    </View>
                    <View style={styles.searchRow}>
                        <View style={styles.searchWrap}>
                            <Text style={styles.searchIcon}>🔍</Text>
                            <TextInput
                                style={styles.searchInput}
                                placeholder="Search Event..."
                                placeholderTextColor="#aaa"
                                value={search}
                                onChangeText={setSearch}
                                onSubmitEditing={() => { setLoading(true); loadEvents(); }}
                                returnKeyType="search"
                            />
                        </View>
                        <TouchableOpacity style={styles.filterBtn} onPress={() => setShowOrgModal(true)}>
                            <Text style={styles.filterBtnText} numberOfLines={1}>
                                {selectedOrg ? selectedOrg.name : 'Filter'} ▼
                            </Text>
                        </TouchableOpacity>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {/* Org filter modal */}
            <Modal visible={showOrgModal} transparent animationType="fade" onRequestClose={() => setShowOrgModal(false)}>
                <TouchableOpacity style={[styles.modalOverlay, { paddingTop: Math.round(height * 0.15) }]} activeOpacity={1} onPress={() => setShowOrgModal(false)}>
                    <View style={styles.modalBox}>
                        <ScrollView nestedScrollEnabled showsVerticalScrollIndicator={false}>
                            <TouchableOpacity
                                style={[styles.modalItem, !selectedOrg && styles.modalItemActive]}
                                onPress={() => { setSelectedOrg(null); setShowOrgModal(false); }}
                            >
                                <Text style={[styles.modalItemText, !selectedOrg && styles.modalItemTextActive]}>
                                    All Organizations
                                </Text>
                            </TouchableOpacity>
                            {orgs.map(org => (
                                <TouchableOpacity
                                    key={org.id}
                                    style={[styles.modalItem, selectedOrg?.id === org.id && styles.modalItemActive]}
                                    onPress={() => { setSelectedOrg(org); setShowOrgModal(false); }}
                                >
                                    <Text style={[styles.modalItemText, selectedOrg?.id === org.id && styles.modalItemTextActive]}>
                                        {org.name}
                                    </Text>
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                </TouchableOpacity>
            </Modal>

            {loading ? (
                <ActivityIndicator style={{ marginTop: 60 }} color="#4A6CF7" size="large" />
            ) : (
                <FlatList
                    data={events}
                    keyExtractor={item => String(item.id)}
                    renderItem={renderEvent}
                    contentContainerStyle={styles.list}
                    showsVerticalScrollIndicator={false}
                    refreshControl={
                        <RefreshControl
                            refreshing={refreshing}
                            onRefresh={() => { setRefreshing(true); loadEvents(); }}
                            colors={['#4A6CF7']}
                        />
                    }
                    ListEmptyComponent={<Text style={styles.empty}>No upcoming events found.</Text>}
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#e8eff7' },
    header: { paddingHorizontal: 16, paddingBottom: 20 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 8 },
    backBtn: { padding: 4, marginRight: 8 },
    backIcon: { color: '#fff', fontSize: 28, lineHeight: 28 },
    headerTitle: { color: '#fff', fontSize: 20, fontWeight: '700' },
    searchRow: { flexDirection: 'row', alignItems: 'center', gap: 5 },
    searchWrap: {
        flex: 1, flexDirection: 'row', alignItems: 'center',
        backgroundColor: '#fff', borderRadius: 12, paddingHorizontal: 14, height: 46,
    },
    searchIcon: { fontSize: 16, marginRight: 8 },
    searchInput: { flex: 1, fontSize: 14, color: '#333' },
    filterBtn: {
        backgroundColor: '#fff', borderRadius: 12, height: 46,
        paddingHorizontal: 12, alignItems: 'center', justifyContent: 'center',
        maxWidth: 110,
    },
    filterBtnText: { fontSize: 12, fontWeight: '600', color: '#4A6CF7' },

    // Org modal
    modalOverlay: {
        flex: 1, backgroundColor: 'rgba(0,0,0,0.35)',
        justifyContent: 'flex-start', paddingHorizontal: 16,
        alignItems: 'flex-end',
    },
    modalBox: {
        backgroundColor: '#fff', borderRadius: 14, overflow: 'hidden',
        elevation: 10, minWidth: 220, maxHeight: 300,
        shadowColor: '#000', shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.15, shadowRadius: 12,
    },
    modalItem: { paddingVertical: 13, paddingHorizontal: 18, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    modalItemActive: { backgroundColor: '#eff6ff' },
    modalItemText: { fontSize: 14, color: '#334155' },
    modalItemTextActive: { color: '#4A6CF7', fontWeight: '700' },

    // Cards
    list: { padding: 16, gap: 14 },
    eventCard: {
        backgroundColor: '#fff', borderRadius: 16,
        flexDirection: 'row', alignItems: 'stretch',
        overflow: 'hidden',
        elevation: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.08, shadowRadius: 6,
    },
    poster: { width: 110, height: 130 },
    posterPlaceholder: { width: 110, height: 130, backgroundColor: '#dde4f0' },
    eventInfo: { flex: 1, padding: 12, justifyContent: 'space-between' },
    eventTitle: { fontSize: 14, fontWeight: '700', color: '#0f2044', marginBottom: 8, lineHeight: 20 },
    metaRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 3 },
    metaText: { fontSize: 12, color: '#334155', flex: 1 },
    viewDetailsWrap: { alignSelf: 'flex-end', marginTop: 8 },
    viewDetailsLink: {
        fontSize: 12, fontWeight: '700', color: '#1e3a6e',
        textDecorationLine: 'underline',
    },
    empty: { textAlign: 'center', marginTop: 60, color: '#888', fontSize: 15 },
});
