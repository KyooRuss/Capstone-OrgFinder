import React, { useState, useContext } from 'react';
import {
    View, Text, TouchableOpacity, StyleSheet,
    ScrollView, Image, Alert, ActivityIndicator,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import * as ImagePicker from 'expo-image-picker';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { AuthContext } from '../context/AuthContext';
import api, { BASE_URL } from '../api/client';

export default function ProfileScreen({ navigation }) {
    const { user, logout, refreshUser } = useContext(AuthContext);
    const [uploading, setUploading] = useState(false);

    const handleLogout = () => {
        Alert.alert('Sign Out', 'Are you sure you want to sign out?', [
            { text: 'Cancel', style: 'cancel' },
            { text: 'Sign Out', style: 'destructive', onPress: logout },
        ]);
    };

    const handlePickPhoto = async () => {
        const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
        if (status !== 'granted') {
            Alert.alert('Permission required', 'Please allow access to your photo library.');
            return;
        }

        const result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ImagePicker.MediaTypeOptions.Images,
            allowsEditing: true,
            aspect: [1, 1],
            quality: 0.8,
        });

        if (result.canceled) return;

        const asset = result.assets[0];
        setUploading(true);
        try {
            const token = await AsyncStorage.getItem('token');
            const form = new FormData();
            form.append('photo', {
                uri: asset.uri,
                name: asset.fileName ?? 'photo.jpg',
                type: asset.mimeType ?? 'image/jpeg',
            });
            // Use fetch instead of axios — axios corrupts multipart boundaries
            // when a default Content-Type header is set on the instance.
            const res = await fetch(`${BASE_URL}/api/profile/photo`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
                body: form,
            });
            if (!res.ok) {
                const json = await res.json().catch(() => ({}));
                throw new Error(json.message ?? 'Upload failed');
            }
            await refreshUser();
        } catch (err) {
            Alert.alert('Upload failed', err.message);
        } finally {
            setUploading(false);
        }
    };

    const yearLabel = user?.year_level ? `${user.year_level}${['st','nd','rd'][user.year_level - 1] || 'th'} Year` : '—';
    const infoTag = (items) => items?.length ? items.join(', ') : '—';

    return (
        <ScrollView style={styles.root}>
        <LinearGradient colors={['#7CB9FF', '#4A6CF7']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.scroll}>
            {/* Back button */}
            <SafeAreaView>
                <TouchableOpacity style={styles.backBtn} onPress={() => navigation.goBack()}>
                    <Text style={styles.backIcon}>‹</Text>
                    <Text style={styles.backLabel}>Profile</Text>
                </TouchableOpacity>
                
                {/* Edit profile button */}
                <TouchableOpacity style={styles.editBtn} onPress={() => navigation.navigate('EditProfile')}>
                    <Text style={styles.editIcon}>✎</Text>
                </TouchableOpacity>
            </SafeAreaView>
        
            {/* Profile card */}
            <View style={styles.card}>
                {/* Avatar with upload overlay */}
                <TouchableOpacity style={styles.avatarWrap} onPress={handlePickPhoto} activeOpacity={0.85} disabled={uploading}>
                    {user?.profile_photo
                        ? <Image source={{ uri: user.profile_photo }} style={styles.avatar} />
                        : <View style={[styles.avatar, styles.avatarFallback]}>
                            <Text style={styles.avatarText}>{user?.first_name?.[0] ?? 'U'}</Text>
                          </View>
                    }
                    <View style={styles.uploadBadge}>
                        {uploading
                            ? <ActivityIndicator size="small" color="#4A6CF7" />
                            : <Text style={styles.uploadIcon}>📷</Text>
                        }
                    </View>
                </TouchableOpacity>

                <Text style={styles.userName}>
                    {`${user?.first_name || ''} ${user?.last_name || ''}`}
                </Text>
                <Text style={styles.userEmail}>{user?.email}</Text>

                <View style={styles.statsRow}>
                    <View style={styles.stat}>
                        <Text style={styles.statValue}>{yearLabel}</Text>
                        <Text style={styles.statLabel}>Year level</Text>
                    </View>
                    <View style={styles.statDivider} />
                    <View style={styles.stat}>
                        <Text style={styles.statValue}>{user?.program || '—'}</Text>
                        <Text style={styles.statLabel}>Program</Text>
                    </View>
                </View>
            </View>
        </LinearGradient>

            {/* Info sections */}
            <View style={styles.infoSection}>
                <Text style={styles.infoLabel}>Interest & Hobby</Text>
                <View style={styles.infoBox}>
                    <Text style={styles.infoText}>{infoTag(user?.interests)}</Text>
                </View>
            </View>

            <View style={styles.infoSection}>
                <Text style={styles.infoLabel}>Skills to improve</Text>
                <View style={styles.infoBox}>
                    <Text style={styles.infoText}>{infoTag(user?.skills)}</Text>
                </View>
            </View>

            <View style={styles.infoSection}>
                <Text style={styles.infoLabel}>Preferred Activities</Text>
                <View style={styles.infoBox}>
                    <Text style={styles.infoText}>{infoTag(user?.activities)}</Text>
                </View>
            </View>

            <TouchableOpacity style={styles.signOutBtn} onPress={handleLogout}>
                <Text style={styles.signOutText}>Sign out</Text>
            </TouchableOpacity>
        
        </ScrollView>

    );
}

const styles = StyleSheet.create({
    root: { flex: 1, backgroundColor: '#f5f6fa' },
    backBtn: {
        flexDirection: 'row', alignItems: 'center',
        paddingHorizontal: 16,
    },
    backIcon: { color: '#fff', fontSize: 28, lineHeight: 28, marginRight: 4 },
    backLabel: { color: '#fff', fontSize: 18, fontWeight: '600', marginLeft: '6' },
    card: {
        alignItems: 'center',
        paddingBottom: 30,
        position: 'relative',
    },
    editBtn: {
        position: 'absolute', right: 16, top: 40,
        backgroundColor: 'rgba(255,255,255,0.2)',
        borderRadius: 20, padding: 8,
        width: 36, height: 36,
        justifyContent: 'flex-start',
        alignItems: 'center',
        
    },
    editIcon: { color: '#fff', fontSize: 18 },
    avatarWrap: {
        marginBottom: 14,
        position: 'relative',
    },
    avatar: { width: 90, height: 90, borderRadius: 44, borderWidth: 3, borderColor: 'rgba(255,255,255,0.5)' },
    avatarFallback: { backgroundColor: 'rgba(255,255,255,0.25)', alignItems: 'center', justifyContent: 'center' },
    avatarText: { color: '#fff', fontSize: 36, fontWeight: '700' },
    uploadBadge: {
        position: 'absolute',
        bottom: 0,
        right: 0,
        backgroundColor: '#fff',
        borderRadius: 14,
        width: 28,
        height: 28,
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.2,
        shadowRadius: 3,
        elevation: 3,
    },
    uploadIcon: { fontSize: 14 },
    userName: { fontSize: 20, fontWeight: '700', color: '#fff', marginBottom: 10 },
    userEmail: { fontSize: 13, color: 'rgba(255,255,255,0.75)', marginBottom: 20 },
    statsRow: { flexDirection: 'row', gap: 24 },
    stat: { alignItems: 'center' },
    statValue: { fontSize: 16, fontWeight: '700', color: '#fff' },
    statLabel: { fontSize: 12, color: 'rgba(255,255,255,0.7)', marginTop: 2 },
    statDivider: { width: 1, backgroundColor: 'rgba(255,255,255,0.3)' },
    infoSection: { paddingHorizontal: 20, marginTop: 16 },
    infoLabel: { fontSize: 14, fontWeight: '700', color: '#1e2f6e', marginBottom: 8 },
    infoBox: {
        backgroundColor: '#fff', borderRadius: 12,
        padding: 14, borderWidth: 1, borderColor: '#eee',
    },
    infoText: { fontSize: 14, color: '#555', lineHeight: 20 },
    signOutBtn: {
        marginHorizontal: 20, marginTop: 32,
        backgroundColor: '#1e3a8a', borderRadius: 28,
        height: 52, alignItems: 'center', justifyContent: 'center',
    },
    signOutText: { color: '#fff', fontSize: 16, fontWeight: '700' },
});
